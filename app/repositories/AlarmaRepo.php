<?php
/**
 * Repository pentru tabela Alarme
 * Câmpuri: id, id_pacient, tip_alarma, valoare_declansare, prag_minim, prag_maxim,
 *          moment_declansare, durata_persistenta, mesaj
 */
class AlarmaRepo {
    
    public static function findById($id) {
        if (isMockMode()) return $GLOBALS['MOCK_ALARME'][$id] ?? null;
        $stmt = db()->prepare('SELECT * FROM Alarme WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function all() {
        if (isMockMode()) {
            $arr = array_values($GLOBALS['MOCK_ALARME']);
            usort($arr, fn($a, $b) => strcmp($b['moment_declansare'], $a['moment_declansare']));
            return $arr;
        }
        return db()->query('SELECT * FROM Alarme ORDER BY moment_declansare DESC')->fetchAll();
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_ALARME'], 
                fn($a) => $a['id_pacient'] == $idPacient));
            usort($arr, fn($a, $b) => strcmp($b['moment_declansare'], $a['moment_declansare']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM Alarme WHERE id_pacient = ? ORDER BY moment_declansare DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    /**
     * Alarme pentru toți pacienții unui medic
     */
    public static function findByMedic($idMedic) {
        $pacienti = PacientRepo::findByMedic($idMedic);
        $idsPacienti = array_column($pacienti, 'id');
        if (empty($idsPacienti)) return [];
        
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_ALARME'], 
                fn($a) => in_array($a['id_pacient'], $idsPacienti)));
            usort($arr, fn($a, $b) => strcmp($b['moment_declansare'], $a['moment_declansare']));
            return $arr;
        }
        
        $placeholders = implode(',', array_fill(0, count($idsPacienti), '?'));
        $sql = "SELECT * FROM Alarme WHERE id_pacient IN ({$placeholders}) ORDER BY moment_declansare DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($idsPacienti);
        return $stmt->fetchAll();
    }
    
    public static function recente($limit = 5, $idMedic = null) {
        $all = $idMedic ? self::findByMedic($idMedic) : self::all();
        return array_slice($all, 0, $limit);
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_ALARME']) ? 1 : max(array_keys($GLOBALS['MOCK_ALARME'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_ALARME'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Alarme 
            (id_pacient, tip_alarma, valoare_declansare, prag_minim, prag_maxim,
             moment_declansare, durata_persistenta, mesaj)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['tip_alarma'],
            $data['valoare_declansare'] ?? null,
            $data['prag_minim'] ?? null, $data['prag_maxim'] ?? null,
            $data['moment_declansare'] ?? date('Y-m-d H:i:s'),
            $data['durata_persistenta'] ?? 0,
            $data['mesaj'] ?? '',
        ]);
        return db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_ALARME'][$id])) return false;
            $GLOBALS['MOCK_ALARME'][$id] = array_merge($GLOBALS['MOCK_ALARME'][$id], $data);
            $GLOBALS['MOCK_ALARME'][$id]['id'] = $id;
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
        $sql = 'UPDATE Alarme SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return db()->prepare($sql)->execute($params);
    }
    
    public static function delete($id) {
        if (isMockMode()) {
            unset($GLOBALS['MOCK_ALARME'][$id]);
            return true;
        }
        $stmt = db()->prepare('DELETE FROM Alarme WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    public static function count($idMedic = null) {
        if ($idMedic) return count(self::findByMedic($idMedic));
        if (isMockMode()) return count($GLOBALS['MOCK_ALARME']);
        return (int)db()->query('SELECT COUNT(*) FROM Alarme')->fetchColumn();
    }
}