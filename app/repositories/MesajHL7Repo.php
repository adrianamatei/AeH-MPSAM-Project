<?php
/**
 * Repository pentru tabela MesajeHL7
 * (Tabela urmează a fi confirmată de Roxana)
 * Câmpuri presupuse: id, tip_mesaj, sursa, destinatie, continut, moment_transmitere, id_pacient
 */
class MesajHL7Repo {
    
    public static function findById($id) {
        if (isMockMode()) return $GLOBALS['MOCK_MESAJE_HL7'][$id] ?? null;
        $stmt = db()->prepare('SELECT * FROM MesajeHL7 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function all() {
        if (isMockMode()) {
            $arr = array_values($GLOBALS['MOCK_MESAJE_HL7']);
            usort($arr, fn($a, $b) => strcmp($b['moment_transmitere'], $a['moment_transmitere']));
            return $arr;
        }
        return db()->query('SELECT * FROM MesajeHL7 ORDER BY moment_transmitere DESC')->fetchAll();
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_MESAJE_HL7'], 
                fn($m) => $m['id_pacient'] == $idPacient));
            usort($arr, fn($a, $b) => strcmp($b['moment_transmitere'], $a['moment_transmitere']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM MesajeHL7 WHERE id_pacient = ? ORDER BY moment_transmitere DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mesaje primite (trimiteri de la medic familie)
     */
    public static function primite() {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MESAJE_HL7'], 
                fn($m) => stripos($m['tip_mesaj'], 'trimitere') !== false));
        }
        $stmt = db()->prepare("SELECT * FROM MesajeHL7 WHERE tip_mesaj LIKE '%Trimitere%' ORDER BY moment_transmitere DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Mesaje trimise (scrisori medicale)
     */
    public static function trimise() {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_MESAJE_HL7'], 
                fn($m) => stripos($m['tip_mesaj'], 'scrisoare') !== false || stripos($m['tip_mesaj'], 'FHIR') !== false));
        }
        $stmt = db()->prepare("SELECT * FROM MesajeHL7 WHERE tip_mesaj LIKE '%Scrisoare%' OR tip_mesaj LIKE '%FHIR%' ORDER BY moment_transmitere DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_MESAJE_HL7']) ? 1 : max(array_keys($GLOBALS['MOCK_MESAJE_HL7'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_MESAJE_HL7'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO MesajeHL7 
            (tip_mesaj, sursa, destinatie, continut, moment_transmitere, id_pacient)
            VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['tip_mesaj'], $data['sursa'], $data['destinatie'],
            $data['continut'], 
            $data['moment_transmitere'] ?? date('Y-m-d H:i:s'),
            $data['id_pacient'] ?? null,
        ]);
        return db()->lastInsertId();
    }
}