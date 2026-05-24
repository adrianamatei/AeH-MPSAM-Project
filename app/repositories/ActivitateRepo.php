<?php
/**
 * Repository pentru tabela Activitati (din SQL-ul lui Darius)
 * Câmpuri: id_activitate, id_pacient, nume_activitate, descriere,
 *          data_programata, ora_programata, este_finalizata
 */
class ActivitateRepo {
    
    public static function findById($id) {
        if (isMockMode()) return $GLOBALS['MOCK_ACTIVITATI'][$id] ?? null;
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_activitate = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_ACTIVITATI'], 
                fn($a) => $a['id_pacient'] == $idPacient));
            usort($arr, fn($a, $b) => strcmp($b['data_programata'] . ' ' . $b['ora_programata'], 
                                              $a['data_programata'] . ' ' . $a['ora_programata']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_pacient = ? 
            ORDER BY data_programata DESC, ora_programata DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function activitatiAzi($idPacient) {
        $today = date('Y-m-d');
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_ACTIVITATI'], 
                fn($a) => $a['id_pacient'] == $idPacient && $a['data_programata'] === $today));
        }
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_pacient = ? AND data_programata = ?
            ORDER BY ora_programata ASC');
        $stmt->execute([$idPacient, $today]);
        return $stmt->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_ACTIVITATI']) ? 1 : max(array_keys($GLOBALS['MOCK_ACTIVITATI'])) + 1;
            $data['id_activitate'] = $newId;
            $GLOBALS['MOCK_ACTIVITATI'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Activitati 
            (id_pacient, nume_activitate, descriere, data_programata, ora_programata, este_finalizata)
            VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['nume_activitate'],
            $data['descriere'] ?? null,
            $data['data_programata'], $data['ora_programata'],
            $data['este_finalizata'] ?? 0,
        ]);
        return db()->lastInsertId();
    }
    
    public static function marcheazaFinalizata($id, $finalizat = 1) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_ACTIVITATI'][$id])) return false;
            $GLOBALS['MOCK_ACTIVITATI'][$id]['este_finalizata'] = (int)$finalizat;
            return true;
        }
        $stmt = db()->prepare('UPDATE Activitati SET este_finalizata = ? WHERE id_activitate = ?');
        return $stmt->execute([(int)$finalizat, $id]);
    }
    
    public static function delete($id) {
        if (isMockMode()) {
            unset($GLOBALS['MOCK_ACTIVITATI'][$id]);
            return true;
        }
        $stmt = db()->prepare('DELETE FROM Activitati WHERE id_activitate = ?');
        return $stmt->execute([$id]);
    }
}