<?php
/**
 * Repository pentru tabela Utilizatori
 * 
 * Câmpuri: id_utilizator, email, parola, rol
 */
class UtilizatorRepo {
    
    /**
     * Caută utilizator după email
     */
    public static function findByEmail($email) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_UTILIZATORI'] as $user) {
                if (strtolower($user['email']) === strtolower($email)) {
                    return $user;
                }
            }
            return null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Utilizatori WHERE LOWER(email) = LOWER(?)');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Caută utilizator după ID
     */
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_UTILIZATORI'][$id] ?? null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Utilizatori WHERE id_utilizator = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Returnează toți utilizatorii
     */
    public static function all() {
        if (isMockMode()) {
            return array_values($GLOBALS['MOCK_UTILIZATORI']);
        }
        
        return db()->query('SELECT * FROM Utilizatori')->fetchAll();
    }
    
    /**
     * Adaugă un utilizator nou
     */
    public static function insert($data) {
        if (isMockMode()) {
            $newId = max(array_keys($GLOBALS['MOCK_UTILIZATORI'])) + 1;
            $data['id_utilizator'] = $newId;
            $GLOBALS['MOCK_UTILIZATORI'][$newId] = $data;
            return $newId;
        }
        
        $stmt = db()->prepare(
            'INSERT INTO Utilizatori (email, parola, rol) VALUES (?, ?, ?)'
        );
        $stmt->execute([$data['email'], $data['parola'], $data['rol']]);
        return db()->lastInsertId();
    }
    
    /**
     * Actualizează parola unui utilizator
     */
    public static function updatePassword($id, $newHashedPassword) {
        if (isMockMode()) {
            if (!isset($GLOBALS['MOCK_UTILIZATORI'][$id])) return false;
            $GLOBALS['MOCK_UTILIZATORI'][$id]['parola'] = $newHashedPassword;
            return true;
        }
        
        $stmt = db()->prepare('UPDATE Utilizatori SET parola = ? WHERE id_utilizator = ?');
        return $stmt->execute([$newHashedPassword, $id]);
    }
}