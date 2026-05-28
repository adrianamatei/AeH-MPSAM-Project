<?php
/**
 * Repository: Masuratori
 * Azure: id_masurare, id_pacient, tip_parametru, valoare(float), 
 *        unitate_masurata, moment_inregistrare(datetime)
 */
class MasuratoriRepo {
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MASURATORI'],
                fn($m) => $m['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Masuratori WHERE id_pacient = ? ORDER BY moment_inregistrare DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByPacientInterval($idPacient, $startDate, $endDate, $tipParametru = null) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MASURATORI'], function($m) use ($idPacient, $startDate, $endDate, $tipParametru) {
                if ($m['id_pacient'] != $idPacient) return false;
                if ($tipParametru && $m['tip_parametru'] != $tipParametru) return false;
                return $m['moment_inregistrare'] >= $startDate && $m['moment_inregistrare'] <= $endDate;
            }));
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
    
    public static function ultimeleValori($idPacient) {
        if (isMockMode()) {
            $result = [];
            foreach (['puls', 'temperatura'] as $tip) {
                $filtered = array_filter($GLOBALS['MOCK_MASURATORI'], 
                    fn($m) => $m['id_pacient'] == $idPacient && $m['tip_parametru'] == $tip);
                if (!empty($filtered)) {
                    usort($filtered, fn($a, $b) => strcmp($b['moment_inregistrare'], $a['moment_inregistrare']));
                    $result[$tip] = reset($filtered);
                }
            }
            return $result;
        }
        $result = [];
        foreach (['puls', 'temperatura'] as $tip) {
            $stmt = db()->prepare('SELECT TOP 1 * FROM Masuratori 
                WHERE id_pacient = ? AND tip_parametru = ? 
                ORDER BY moment_inregistrare DESC');
            $stmt->execute([$idPacient, $tip]);
            $row = $stmt->fetch();
            if ($row) $result[$tip] = $row;
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
            $data['id_pacient'], $data['tip_parametru'], $data['valoare'],
            $data['unitate_masurata'], $data['moment_inregistrare'],
        ]);
        return (int)db()->lastInsertId();
    }
}
