<?php
/**
 * Repository: Activitati
 * Azure: id_activitate, id_pacient, nume_activitate, descriere, 
 *        data_programata(date), ora_programata, este_finalizata(bit)
 */
class ActivitateRepo {
    
    public static function findById($id) {
        if (isMockMode()) { return $GLOBALS['MOCK_ACTIVITATI'][$id] ?? null; }
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_activitate = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_ACTIVITATI'], fn($a) => $a['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_pacient = ? ORDER BY data_programata DESC, ora_programata');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function activitatiAzi($idPacient) {
        if (isMockMode()) {
            $azi = date('Y-m-d');
            return array_values(array_filter($GLOBALS['MOCK_ACTIVITATI'], 
                fn($a) => $a['id_pacient'] == $idPacient && $a['data_programata'] == $azi));
        }
        $stmt = db()->prepare('SELECT * FROM Activitati WHERE id_pacient = ? AND data_programata = CAST(GETDATE() AS DATE) ORDER BY ora_programata');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_ACTIVITATI']) ? 1 : max(array_keys($GLOBALS['MOCK_ACTIVITATI'])) + 1;
            $data['id_activitate'] = $newId;
            $GLOBALS['MOCK_ACTIVITATI'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Activitati (id_pacient, nume_activitate, descriere, data_programata, ora_programata, este_finalizata) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['id_pacient'], $data['nume_activitate'], $data['descriere'] ?? null, $data['data_programata'], $data['ora_programata'], $data['este_finalizata'] ?? 0]);
        return (int)db()->lastInsertId();
    }
    
    public static function marcheazaFinalizata($id, $finalizata = 1) {
        if (isMockMode()) {
            if (isset($GLOBALS['MOCK_ACTIVITATI'][$id])) $GLOBALS['MOCK_ACTIVITATI'][$id]['este_finalizata'] = $finalizata;
            return true;
        }
        $stmt = db()->prepare('UPDATE Activitati SET este_finalizata = ? WHERE id_activitate = ?');
        return $stmt->execute([$finalizata, $id]);
    }
}
