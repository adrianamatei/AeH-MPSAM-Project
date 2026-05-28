<?php
/**
 * Repository: Consultatii
 * Azure: id, id_pacient, id_medic, data_consultatie(datetime), motiv_prezentare,
 *        simptome(text), diagnostic, id_recomandare, trimiteri(text), retete(text)
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
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_CONSULTATII'],
                fn($c) => $c['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Consultatii WHERE id_pacient = ? ORDER BY data_consultatie DESC');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_CONSULTATII'],
                fn($c) => $c['id_medic'] == $idMedic));
        }
        $stmt = db()->prepare('SELECT * FROM Consultatii WHERE id_medic = ? ORDER BY data_consultatie DESC');
        $stmt->execute([$idMedic]);
        return $stmt->fetchAll();
    }
    
    public static function recente($limit = 5, $idMedic = null) {
        if (isMockMode()) {
            $data = $idMedic 
                ? array_filter($GLOBALS['MOCK_CONSULTATII'], fn($c) => $c['id_medic'] == $idMedic)
                : $GLOBALS['MOCK_CONSULTATII'];
            usort($data, fn($a, $b) => strcmp($b['data_consultatie'], $a['data_consultatie']));
            return array_slice($data, 0, $limit);
        }
        $limit = (int)$limit;
        if ($idMedic) {
            $stmt = db()->prepare("SELECT TOP {$limit} * FROM Consultatii WHERE id_medic = ? ORDER BY data_consultatie DESC");
            $stmt->execute([$idMedic]);
        } else {
            $stmt = db()->query("SELECT TOP {$limit} * FROM Consultatii ORDER BY data_consultatie DESC");
        }
        return $stmt->fetchAll();
    }
    
    public static function count($idMedic = null) {
        if (isMockMode()) {
            if (!$idMedic) return count($GLOBALS['MOCK_CONSULTATII']);
            return count(array_filter($GLOBALS['MOCK_CONSULTATII'], fn($c) => $c['id_medic'] == $idMedic));
        }
        if ($idMedic) {
            $stmt = db()->prepare('SELECT COUNT(*) FROM Consultatii WHERE id_medic = ?');
            $stmt->execute([$idMedic]);
            return (int)$stmt->fetchColumn();
        }
        return (int)db()->query('SELECT COUNT(*) FROM Consultatii')->fetchColumn();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_CONSULTATII']) ? 1 : max(array_keys($GLOBALS['MOCK_CONSULTATII'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_CONSULTATII'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Consultatii 
            (id_pacient, id_medic, data_consultatie, motiv_prezentare, simptome, diagnostic, id_recomandare, trimiteri, retete)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['id_medic'], $data['data_consultatie'],
            $data['motiv_prezentare'], $data['simptome'], $data['diagnostic'],
            $data['id_recomandare'] ?? $data['id_recomandari'] ?? null,
            $data['trimiteri'], $data['retete'],
        ]);
        return (int)db()->lastInsertId();
    }
}