<?php
/**
 * Repository pentru tabela Dispozitive
 * Câmpuri: id, tip_dispozitiv, id_pacient, stare, detalii
 */
class DispozitivRepo {
    
    public static function findById($id) {
        if (isMockMode()) return $GLOBALS['MOCK_DISPOZITIVE'][$id] ?? null;
        $stmt = db()->prepare('SELECT * FROM Dispozitive WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function all() {
        if (isMockMode()) return array_values($GLOBALS['MOCK_DISPOZITIVE']);
        return db()->query('SELECT * FROM Dispozitive ORDER BY id')->fetchAll();
    }
    
    public static function findByPacient($idPacient) {
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_DISPOZITIVE'], 
                fn($d) => $d['id_pacient'] == $idPacient));
        }
        $stmt = db()->prepare('SELECT * FROM Dispozitive WHERE id_pacient = ?');
        $stmt->execute([$idPacient]);
        return $stmt->fetchAll();
    }
    
    public static function findByMedic($idMedic) {
        $pacienti = PacientRepo::findByMedic($idMedic);
        $idsPacienti = array_column($pacienti, 'id');
        if (empty($idsPacienti)) return [];
        
        if (isMockMode()) {
            return array_values(array_filter($GLOBALS['MOCK_DISPOZITIVE'], 
                fn($d) => in_array($d['id_pacient'], $idsPacienti)));
        }
        $placeholders = implode(',', array_fill(0, count($idsPacienti), '?'));
        $stmt = db()->prepare("SELECT * FROM Dispozitive WHERE id_pacient IN ({$placeholders})");
        $stmt->execute($idsPacienti);
        return $stmt->fetchAll();
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_DISPOZITIVE']) ? 1 : max(array_keys($GLOBALS['MOCK_DISPOZITIVE'])) + 1;
            $data['id'] = $newId;
            $GLOBALS['MOCK_DISPOZITIVE'][$newId] = $data;
            return $newId;
        }
        $stmt = db()->prepare('INSERT INTO Dispozitive (tip_dispozitiv, id_pacient, stare, detalii) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['tip_dispozitiv'], $data['id_pacient'],
            $data['stare'] ?? 'activ', $data['detalii'] ?? '',
        ]);
        return db()->lastInsertId();
    }
    
    public static function update($id, $data) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_DISPOZITIVE'][$id])) return false;
            $GLOBALS['MOCK_DISPOZITIVE'][$id] = array_merge($GLOBALS['MOCK_DISPOZITIVE'][$id], $data);
            $GLOBALS['MOCK_DISPOZITIVE'][$id]['id'] = $id;
            return true;
        }
        $stmt = db()->prepare('UPDATE Dispozitive SET tip_dispozitiv = ?, id_pacient = ?, stare = ?, detalii = ? WHERE id = ?');
        return $stmt->execute([
            $data['tip_dispozitiv'], $data['id_pacient'],
            $data['stare'] ?? 'activ', $data['detalii'] ?? '', $id,
        ]);
    }
    
    public static function delete($id) {
        if (isMockMode()) {
            unset($GLOBALS['MOCK_DISPOZITIVE'][$id]);
            return true;
        }
        $stmt = db()->prepare('DELETE FROM Dispozitive WHERE id = ?');
        return $stmt->execute([$id]);
    }
}