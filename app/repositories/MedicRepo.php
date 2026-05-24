<?php
/**
 * Repository pentru tabela Medici
 * 
 * Câmpuri: id, id_utilizator, nume, prenume, specializare, telefon, email
 */
class MedicRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_MEDICI'][$id] ?? null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Medici WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function findByUtilizator($idUtilizator) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_MEDICI'] as $medic) {
                if ($medic['id_utilizator'] == $idUtilizator) {
                    return $medic;
                }
            }
            return null;
        }
        
        $stmt = db()->prepare('SELECT * FROM Medici WHERE id_utilizator = ?');
        $stmt->execute([$idUtilizator]);
        return $stmt->fetch() ?: null;
    }
    
    public static function all() {
        if (isMockMode()) {
            return array_values($GLOBALS['MOCK_MEDICI']);
        }
        
        return db()->query('SELECT * FROM Medici ORDER BY nume, prenume')->fetchAll();
    }
    
    /**
     * Numărul total de medici
     */
    public static function count() {
        if (isMockMode()) {
            return count($GLOBALS['MOCK_MEDICI']);
        }
        
        return (int)db()->query('SELECT COUNT(*) FROM Medici')->fetchColumn();
    }
    
    /**
     * Returnează nume complet medic (pt afișare)
     */
    public static function fullName($medic) {
        if (!$medic) return '-';
        return 'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
    }
}