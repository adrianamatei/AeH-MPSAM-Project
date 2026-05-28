<?php
/**
 * Repository: Dispozitive
 * Azure: id, tip_dispozitiv, id_pacient, stare, detalii
 */
class DispozitivRepo {
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_DISPOZITIVE'], fn($d) => $d['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Dispozitive WHERE id_pacient = ?');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            $pacienti = PacientRepo::findByMedic($idMedic);
            $ids = array_column($pacienti, 'id');
            return array_values(array_filter($GLOBALS['MOCK_DISPOZITIVE'], fn($d) => in_array($d['id_pacient'], $ids)));
        }
        $stmt = db()->prepare('SELECT d.* FROM Dispozitive d INNER JOIN Pacient p ON d.id_pacient = p.id WHERE p.id_medic = ?');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
}
