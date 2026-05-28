<?php
/**
 * Repository: Recomandari
 * Azure: id_recomandare, id_pacient, id_medic, tip_recomandare, indicatii(text)
 */
class RecomandareRepo {
    
    public static function findById($id) {
        if (isMockMode()) { return $GLOBALS['MOCK_RECOMANDARI'][$id] ?? null; }
        $stmt = db()->prepare('SELECT * FROM Recomandari WHERE id_recomandare = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_RECOMANDARI'], fn($r) => $r['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Recomandari WHERE id_pacient = ?');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_RECOMANDARI'], fn($r) => $r['id_medic'] == $idMedic));
        }
        $stmt = db()->prepare('SELECT * FROM Recomandari WHERE id_medic = ?');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_RECOMANDARI']) ? 1 : max(array_keys($GLOBALS['MOCK_RECOMANDARI'])) + 1;
            $data['id_recomandare'] = $newId;
            $GLOBALS['MOCK_RECOMANDARI'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Recomandari (id_pacient, id_medic, tip_recomandare, indicatii) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['id_pacient'], $data['id_medic'], $data['tip_recomandare'], $data['indicatii']]);
        return (int)db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_RECOMANDARI'][$id])) return false;
            $GLOBALS['MOCK_RECOMANDARI'][$id] = array_merge($GLOBALS['MOCK_RECOMANDARI'][$id], $data);
            return true;
        }
        $stmt = db()->prepare('UPDATE Recomandari SET tip_recomandare=?, indicatii=? WHERE id_recomandare=?');
        return $stmt->execute([$data['tip_recomandare'], $data['indicatii'], $id]);
    }
    
    public static function delete($id) {
        if (isMockMode()) { unset($GLOBALS['MOCK_RECOMANDARI'][$id]); return true; }
        return db()->prepare('DELETE FROM Recomandari WHERE id_recomandare = ?')->execute([$id]);
    }
}
