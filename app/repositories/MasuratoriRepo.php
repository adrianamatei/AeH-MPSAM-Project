<?php
/**
 * Repository pentru tabela Masuratori
 * Câmpuri: id_masurare, id_pacient, tip_parametru, valoare, unitate_masurata, moment_inregistrare
 */
class MasuratoriRepo {
    
    public static function findByPacient($idPacient, $limit = null) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_MASURATORI'], 
                fn($m) => $m['id_pacient'] == $idPacient));
            usort($arr, fn($a, $b) => strcmp($b['moment_inregistrare'], $a['moment_inregistrare']));
            return $limit ? array_slice($arr, 0, $limit) : $arr;
        }
        $sql = 'SELECT * FROM Masuratori WHERE id_pacient = ? ORDER BY moment_inregistrare DESC';
        if ($limit) $sql .= ' OFFSET 0 ROWS FETCH NEXT ' . (int)$limit . ' ROWS ONLY';
        $stmt = db()->prepare($sql);
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    /**
     * Returnează măsurătorile dintr-un interval de timp pentru un pacient
     */
    public static function findByPacientInterval($idPacient, $startDate, $endDate, $tipParametru = null) {
        if (isMockMode()) {
            $arr = array_filter($GLOBALS['MOCK_MASURATORI'], function($m) use ($idPacient, $startDate, $endDate, $tipParametru) {
                if ($m['id_pacient'] != $idPacient) return false;
                if ($m['moment_inregistrare'] < $startDate) return false;
                if ($m['moment_inregistrare'] > $endDate) return false;
                if ($tipParametru && $m['tip_parametru'] !== $tipParametru) return false;
                return true;
            });
            $arr = array_values($arr);
            usort($arr, fn($a, $b) => strcmp($a['moment_inregistrare'], $b['moment_inregistrare']));
            return $arr;
        }
        $sql = 'SELECT * FROM Masuratori WHERE id_pacient = ? AND moment_inregistrare BETWEEN ? AND ?';
        $params = [$idPacient, $startDate, $endDate];
        if ($tipParametru) {
            $sql .= ' AND tip_parametru = ?';
            $params[] = $tipParametru;
        }
        $sql .= ' ORDER BY moment_inregistrare ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Ultimele valori pe fiecare tip de parametru pentru un pacient
     */
    public static function ultimeleValori($idPacient) {
        $masuratori = self::findByPacient($idPacient);
        $result = [];
        foreach ($masuratori as $m) {
            if (!isset($result[$m['tip_parametru']])) {
                $result[$m['tip_parametru']] = $m;
            }
        }
        return $result;
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_MASURATORI']) ? 1 : max(array_keys($GLOBALS['MOCK_MASURATORI'])) + 1;
            $data['id_masurare'] = $newId;
            $GLOBALS['MOCK_MASURATORI'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Masuratori 
            (id_pacient, tip_parametru, valoare, unitate_masurata, moment_inregistrare)
            VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['tip_parametru'],
            $data['valoare'], $data['unitate_masurata'],
            $data['moment_inregistrare'] ?? date('Y-m-d H:i:s'),
        ]);
        return db()->lastInsertId();
    }
    
    public static function count($idPacient = null) {
        if (isMockMode()) {
            if ($idPacient) return count(self::findByPacient($idPacient));
            return count($GLOBALS['MOCK_MASURATORI']);
        }
        if ($idPacient) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM Masuratori WHERE id_pacient = ?');
            $stmt->execute([$idPacient]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query('SELECT COUNT(*) FROM Masuratori')->fetchColumn();
    }
}