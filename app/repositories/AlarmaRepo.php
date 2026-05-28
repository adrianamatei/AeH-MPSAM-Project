<?php
/**
 * Repository: Alarme
 * Azure: id, id_pacient, tip_alarma, valoare_declansatoare(float), 
 *        prag_min(float), prag_max(float), moment_declansare(datetime),
 *        durata_persistenta(int), mesaj
 * ATENȚIE: Azure are valoare_declansatoare (nu valoare_declansare)
 *          și prag_min/prag_max (nu prag_minim/prag_maxim)
 */
class AlarmaRepo {
    
    /**
     * Normalizează un rând din DB la cheile folosite în pagini
     */
    private static function normalize($row) {
        if (!$row) return null;
        // Mapăm numele Azure → cele folosite în paginile PHP
        $row['valoare_declansare'] = $row['valoare_declansatoare'] ?? $row['valoare_declansare'] ?? null;
        $row['prag_minim'] = $row['prag_min'] ?? $row['prag_minim'] ?? null;
        $row['prag_maxim'] = $row['prag_max'] ?? $row['prag_maxim'] ?? null;
        return $row;
    }
    
    private static function normalizeAll($rows) {
        return array_map([self::class, 'normalize'], $rows);
    }
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_ALARME'][$id] ?? null;
        }
        $stmt = db()->prepare('SELECT * FROM Alarme WHERE id = ?');
        $stmt->execute([$id]);
        return self::normalize($stmt->fetch() ?: null);
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_ALARME'],
                fn($a) => $a['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Alarme WHERE id_pacient = ? ORDER BY moment_declansare DESC');
        $stmt->execute([$idPacient]);
        return self::normalizeAll($stmt->fetchAll());
    }
    
    public static function findByMedic($idMedic) {
        if (isMockMode()) {
            $pacienti = PacientRepo::findByMedic($idMedic);
            $ids = array_column($pacienti, 'id');
            return array_values(array_filter($GLOBALS['MOCK_ALARME'],
                fn($a) => in_array($a['id_pacient'], $ids)));
        }
        $stmt = db()->prepare('SELECT a.* FROM Alarme a 
            INNER JOIN Pacient p ON a.id_pacient = p.id 
            WHERE p.id_medic = ? ORDER BY a.moment_declansare DESC');
        $stmt->execute([$idMedic]);
        return self::normalizeAll($stmt->fetchAll());
    }
    
    public static function recente($limit = 5, $idMedic = null) {
        if (isMockMode()) {
            $data = $idMedic ? self::findByMedic($idMedic) : array_values($GLOBALS['MOCK_ALARME']);
            usort($data, fn($a, $b) => strcmp($b['moment_declansare'], $a['moment_declansare']));
            return array_slice($data, 0, $limit);
        }
        $limit = (int)$limit;
        if ($idMedic) {
            $stmt = db()->prepare("SELECT TOP {$limit} a.* FROM Alarme a 
                INNER JOIN Pacient p ON a.id_pacient = p.id 
                WHERE p.id_medic = ? ORDER BY a.moment_declansare DESC");
            $stmt->execute([$idMedic]);
        } else {
            $stmt = db()->query("SELECT TOP {$limit} * FROM Alarme ORDER BY moment_declansare DESC");
        }
        return self::normalizeAll($stmt->fetchAll());
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_ALARME']) ? 1 : max(array_keys($GLOBALS['MOCK_ALARME'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_ALARME'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Alarme 
            (id_pacient, tip_alarma, valoare_declansatoare, prag_min, prag_max, moment_declansare, durata_persistenta, mesaj)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['id_pacient'], $data['tip_alarma'],
            $data['valoare_declansare'] ?? $data['valoare_declansatoare'] ?? 0,
            $data['prag_minim'] ?? $data['prag_min'] ?? 0,
            $data['prag_maxim'] ?? $data['prag_max'] ?? 0,
            $data['moment_declansare'], $data['durata_persistenta'], $data['mesaj'],
        ]);
        return (int)db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_ALARME'][$id])) return false;
            $GLOBALS['MOCK_ALARME'][$id] = array_merge($GLOBALS['MOCK_ALARME'][$id], $data);
            return true;
        }
        $stmt = db()->prepare('UPDATE Alarme SET 
            tip_alarma=?, valoare_declansatoare=?, prag_min=?, prag_max=?, durata_persistenta=?, mesaj=?
            WHERE id=?');
        return $stmt->execute([
            $data['tip_alarma'],
            $data['valoare_declansare'] ?? $data['valoare_declansatoare'] ?? 0,
            $data['prag_minim'] ?? $data['prag_min'] ?? 0,
            $data['prag_maxim'] ?? $data['prag_max'] ?? 0,
            $data['durata_persistenta'], $data['mesaj'], $id,
        ]);
    }
    
    public static function delete($id) {
        if (isMockMode()) { unset($GLOBALS['MOCK_ALARME'][$id]); return true; }
        return db()->prepare('DELETE FROM Alarme WHERE id = ?')->execute([$id]);
    }
}