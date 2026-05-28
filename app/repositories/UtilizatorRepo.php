<?php
/**
 * Repository: Utilizatori
 * Azure: id, email, parola, rol
 */
class UtilizatorRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_UTILIZATORI'][$id] ?? null;
        }
        $stmt = db()->prepare('SELECT * FROM Utilizatori WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByEmail($email) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_UTILIZATORI'] as $u) {
                if (strtolower($u['email']) === strtolower($email)) return $u;
            }
            return null;
        }
        $stmt = db()->prepare('SELECT * FROM Utilizatori WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
    
    public static function updatePassword($id, $hashedPassword) {
        if (isMockMode()) {
            $GLOBALS['MOCK_UTILIZATORI'][$id]['parola'] = $hashedPassword;
            return true;
        }
        $stmt = db()->prepare('UPDATE Utilizatori SET parola = ? WHERE id = ?');
        return $stmt->execute([$hashedPassword, $id]);
    }
}
