<?php
/**
 * Repository pentru tabela Pacient (singular, ca în CREATE-ul lui Darius)
 * 
 * Câmpuri: id, id_utilizator, id_medic, nume, prenume, cnp, varsta,
 *          strada, oras, judet, telefon, email, profesie, loc_de_munca,
 *          istoric_medical, alergii
 */
class PacientRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_PACIENT'][$id] ?? null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Pacient WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByUtilizator($idUtilizator) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_PACIENT'] as $pacient) {
                if ($pacient['id_utilizator'] == $idUtilizator) {
                    return $pacient;
                }
            }
            return null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Pacient WHERE id_utilizator = ?');
        $stmt->execute([$idUtilizator]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByCnp($cnp) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_PACIENT'] as $pacient) {
                if ($pacient['cnp'] === $cnp) {
                    return $pacient;
                }
            }
            return null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Pacient WHERE cnp = ?');
        $stmt->execute([$cnp]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Toți pacienții
     */
    public static function all() {
        if (isMockMode()) {
            return array_values($GLOBALS['MOCK_PACIENT']);
        }
        
        return db()->query('SELECT * FROM Pacient ORDER BY nume, prenume')->fetchAll();
    }
    
    /**
     * Pacienții asociați unui medic
     */
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_PACIENT'], function($p) use ($idMedic) {
                return $p['id_medic'] == $idMedic;
            }));
        }
        
        $stmt = db()->prepare('SELECT * FROM Pacient WHERE id_medic = ? ORDER BY nume, prenume');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
    
    /**
     * Caută pacienți după text (nume, prenume, CNP)
     */
    public static function search($query, $idMedic = null) {
        $query = mb_strtolower(trim($query));
        if (empty($query)) {
            return $idMedic ? self::findByMedic($idMedic) : self::all();
        }
        
        if (isMockMode()) {
            $source = $idMedic ? self::findByMedic($idMedic) : self::all();
            return array_values(array_filter($source, function($p) use ($query) {
                $haystack = mb_strtolower(
                    ($p['nume'] ?? '') . ' ' . 
                    ($p['prenume'] ?? '') . ' ' . 
                    ($p['cnp'] ?? '')
                );
                return mb_strpos($haystack, $query) !== false;
            }));
        }
        
        $sql = 'SELECT * FROM Pacient WHERE 
                (LOWER(nume) LIKE ? OR LOWER(prenume) LIKE ? OR cnp LIKE ?)';
        $params = ["%{$query}%", "%{$query}%", "%{$query}%"];
        
        if ($idMedic) {
            $sql .= ' AND id_medic = ?';
            $params[] = $idMedic;
        }
        $sql .= ' ORDER BY nume, prenume';
        
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Adaugă un pacient nou
     */
    public static function insert($data) {
        if (isMockMode()) {
            $newId = max(array_keys($GLOBALS['MOCK_PACIENT'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_PACIENT'][$newId] = $data;
            // Creează automat și pragurile default
            $GLOBALS['MOCK_PRAGURI_PACIENT'][$newId] = [
                'id_pacient' => $newId,
                'max_puls' => 93.0,
                'min_puls' => 68.0,
                'max_temp' => 38.5,
            ];
            return $newId;
        }
        
        $stmt = db()->prepare('INSERT INTO Pacient 
            (id_utilizator, id_medic, nume, prenume, cnp, varsta, strada, oras, judet,
             telefon, email, profesie, loc_de_munca, istoric_medical, alergii)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_utilizator'] ?? null,
            $data['id_medic'] ?? null,
            $data['nume'], $data['prenume'], $data['cnp'],
            $data['varsta'] ?? null,
            $data['strada'] ?? null, $data['oras'] ?? null, $data['judet'] ?? null,
            $data['telefon'] ?? null, $data['email'] ?? null,
            $data['profesie'] ?? null, $data['loc_de_munca'] ?? null,
            $data['istoric_medical'] ?? null, $data['alergii'] ?? null,
        ]);
        return db()->lastInsertId();
    }
    
    /**
     * Actualizează un pacient
     */
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_PACIENT'][$id])) return false;
            $GLOBALS['MOCK_PACIENT'][$id] = array_merge($GLOBALS['MOCK_PACIENT'][$id], $data);
            $GLOBALS['MOCK_PACIENT'][$id]['id'] = $id;
            return true;
        }
        
        $fields = [];
        $params = [];
        foreach ($data as $col => $val) {
            if ($col === 'id') continue;
            $fields[] = "{$col} = ?";
            $params[] = $val;
        }
        $params[] = $id;
        
        $sql = 'UPDATE Pacient SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return db()->prepare($sql)->execute($params);
    }
    
    /**
     * Șterge un pacient
     */
    public static function delete($id) {
        if (isMockMode()) {
            unset($GLOBALS['MOCK_PACIENT'][$id]);
            unset($GLOBALS['MOCK_PRAGURI_PACIENT'][$id]);
            return true;
        }
        
        $stmt = db()->prepare('DELETE FROM Pacient WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    /**
     * Returnează nume complet
     */
    public static function fullName($pacient) {
        if (!$pacient) return '-';
        return trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? ''));
    }
    
    /**
     * Număr total pacienți (eventual filtrat după medic)
     */
    public static function count($idMedic = null) {
        if (isMockMode()) {
            if ($idMedic) return count(self::findByMedic($idMedic));
            return count($GLOBALS['MOCK_PACIENT']);
        }
        
        if ($idMedic) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM Pacient WHERE id_medic = ?');
            $stmt->execute([$idMedic]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query('SELECT COUNT(*) FROM Pacient')->fetchColumn();
    }
}