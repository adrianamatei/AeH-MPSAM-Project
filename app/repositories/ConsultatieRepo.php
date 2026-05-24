<?php
/**
 * Repository pentru tabela Consultatii
 * 
 * Câmpuri: id, id_pacient, id_medic, data_consultatie, motiv_prezentare,
 *          simptome, diagnostic, retete, id_recomandari, trimiteri
 */
class ConsultatieRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_CONSULTATII'][$id] ?? null;
        }
        $stmt = db()->prepare('SELECT * FROM Consultatii WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function all() {
        if (isMockMode()) {
            $arr = array_values($GLOBALS['MOCK_CONSULTATII']);
            usort($arr, fn($a, $b) => strcmp($b['data_consultatie'], $a['data_consultatie']));
            return $arr;
        }
        return db()->query('SELECT * FROM Consultatii ORDER BY data_consultatie DESC')->fetchAll();
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_CONSULTATII'], 
                fn($c) => $c['id_pacient'] == $idPacient));
            usort($arr, fn($a, $b) => strcmp($b['data_consultatie'], $a['data_consultatie']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM Consultatii WHERE id_pacient = ? ORDER BY data_consultatie DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_CONSULTATII'], 
                fn($c) => $c['id_medic'] == $idMedic));
            usort($arr, fn($a, $b) => strcmp($b['data_consultatie'], $a['data_consultatie']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM Consultatii WHERE id_medic = ? ORDER BY data_consultatie DESC');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
    
    public static function recente($limit = 5, $idMedic = null) {
        $all = $idMedic ? self::findByMedic($idMedic) : self::all();
        return array_slice($all, 0, $limit);
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_CONSULTATII']) ? 1 : max(array_keys($GLOBALS['MOCK_CONSULTATII'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_CONSULTATII'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Consultatii 
            (id_pacient, id_medic, data_consultatie, motiv_prezentare, simptome,
             diagnostic, retete, id_recomandari, trimiteri)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['id_medic'], $data['data_consultatie'],
            $data['motiv_prezentare'] ?? '', $data['simptome'] ?? '',
            $data['diagnostic'] ?? '', $data['retete'] ?? '',
            $data['id_recomandari'] ?? null, $data['trimiteri'] ?? '',
        ]);
        return db()->lastInsertId();
    }
    
    public static function count($idMedic = null) {
        if (isMockMode()) {
            if ($idMedic) return count(self::findByMedic($idMedic));
            return count($GLOBALS['MOCK_CONSULTATII']);
        }
        if ($idMedic) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM Consultatii WHERE id_medic = ?');
            $stmt->execute([$idMedic]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query('SELECT COUNT(*) FROM Consultatii')->fetchColumn();
    }
}