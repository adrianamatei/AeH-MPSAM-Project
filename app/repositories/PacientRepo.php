<?php
/**
 * Repository: Pacient
 * Azure: id, id_medic, nume, prenume, varsta, CNP, strada, oras, judet, telefon,
 *        profesie, loc_de_munca, istoric_medical, alergii, id_utilizator
 * ATENȚIE: CNP e cu majuscule în Azure
 */
class PacientRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_PACIENT'][$id] ?? null;
        }
        $stmt = db()->prepare('SELECT *, CNP as cnp FROM Pacient WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByUtilizator($idUtilizator) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_PACIENT'] as $p) {
                if (($p['id_utilizator'] ?? null) == $idUtilizator) return $p;
            }
            return null;
        }
        $stmt = db()->prepare('SELECT *, CNP as cnp FROM Pacient WHERE id_utilizator = ?');
        $stmt->execute([$idUtilizator]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_PACIENT'], 
                fn($p) => $p['id_medic'] == $idMedic));
        }
        $stmt = db()->prepare('SELECT *, CNP as cnp FROM Pacient WHERE id_medic = ? ORDER BY nume, prenume');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
    
    public static function findByCnp($cnp) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_PACIENT'] as $p) {
                if ($p['cnp'] == $cnp) return $p;
            }
            return null;
        }
        $stmt = db()->prepare('SELECT *, CNP as cnp FROM Pacient WHERE CNP = ?');
        $stmt->execute([$cnp]);
        return $stmt->fetch() ?: null;
    }
    
    public static function search($query, $idMedic = null) {
        if (isMockMode()) {
            $q = mb_strtolower($query);
            return array_values(array_filter($GLOBALS['MOCK_PACIENT'], function($p) use ($q, $idMedic) {
                if ($idMedic && $p['id_medic'] != $idMedic) return false;
                return str_contains(mb_strtolower($p['nume'] . ' ' . $p['prenume'] . ' ' . $p['cnp']), $q);
            }));
        }
        $like = '%' . $query . '%';
        $sql = 'SELECT *, CNP as cnp FROM Pacient WHERE (nume LIKE ? OR prenume LIKE ? OR CNP LIKE ?)';
        $params = [$like, $like, $like];
        if ($idMedic) {
            $sql .= ' AND id_medic = ?';
            $params[] = $idMedic;
        }
        $sql .= ' ORDER BY nume, prenume';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function count($idMedic = null) {
        if (isMockMode()) {
            if (!$idMedic) return count($GLOBALS['MOCK_PACIENT']);
            return count(array_filter($GLOBALS['MOCK_PACIENT'], fn($p) => $p['id_medic'] == $idMedic));
        }
        if ($idMedic) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM Pacient WHERE id_medic = ?');
            $stmt->execute([$idMedic]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query('SELECT COUNT(*) FROM Pacient')->fetchColumn();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_PACIENT']) ? 1 : max(array_keys($GLOBALS['MOCK_PACIENT'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_PACIENT'][$newId] = $data;
            $GLOBALS['MOCK_PRAGURI_PACIENT'][$newId] = [
                'id_pacient' => $newId,
                'max_puls' => 93.0,
                'min_puls' => 68.0,
                'max_temp' => 38.5,
            ];
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Pacient 
            (id_medic, nume, prenume, varsta, CNP, strada, oras, judet, telefon, profesie, loc_de_munca, istoric_medical, alergii, id_utilizator)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_medic'], $data['nume'], $data['prenume'], $data['varsta'],
            $data['cnp'] ?? $data['CNP'], $data['strada'], $data['oras'], $data['judet'],
            $data['telefon'], $data['profesie'], $data['loc_de_munca'],
            $data['istoric_medical'], $data['alergii'], $data['id_utilizator'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_PACIENT'][$id])) return false;
            $GLOBALS['MOCK_PACIENT'][$id] = array_merge($GLOBALS['MOCK_PACIENT'][$id], $data);
            return true;
        }
        $stmt = db()->prepare('UPDATE Pacient SET 
            nume=?, prenume=?, varsta=?, CNP=?, strada=?, oras=?, judet=?, telefon=?,
            profesie=?, loc_de_munca=?, istoric_medical=?, alergii=?
            WHERE id=?');
        return $stmt->execute([
            $data['nume'], $data['prenume'], $data['varsta'],
            $data['cnp'] ?? $data['CNP'] ?? '', $data['strada'], $data['oras'], $data['judet'],
            $data['telefon'], $data['profesie'], $data['loc_de_munca'],
            $data['istoric_medical'], $data['alergii'], $id,
        ]);
    }
    
    public static function delete($id) {
        if (isMockMode()) {
            unset($GLOBALS['MOCK_PACIENT'][$id]);
            return true;
        }
        $stmt = db()->prepare('DELETE FROM Pacient WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public static function fullName($pacient) {
        if (!$pacient) return '-';
        return trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? ''));
    }
}
