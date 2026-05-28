<?php
/**
 * Repository: Medic
 * Azure: id_medic, nume, prenume, specializare, telefon
 * ATENȚIE: PK = id_medic (nu id), tabela = Medic (nu Medici), fără email/id_utilizator
 */
class MedicRepo {
    
    public static function findById($id) {
        if (isMockMode()) {
            return $GLOBALS['MOCK_MEDICI'][$id] ?? null;
        }
        $stmt = db()->prepare('SELECT id_medic, nume, prenume, specializare, telefon FROM Medic WHERE id_medic = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            // Mapăm id_medic → id pentru compatibilitate internă
            $row['id'] = $row['id_medic'];
        }
        return $row ?: null;
    }
    
    public static function findByUtilizator($idUtilizator) {
        if (isMockMode()) {
            foreach ($GLOBALS['MOCK_MEDICI'] as $medic) {
                if (($medic['id_utilizator'] ?? null) == $idUtilizator) return $medic;
            }
            return null;
        }
        // Azure nu are id_utilizator pe Medic — căutăm prin Pacient sau altă logică
        // Temporar: returnăm primul medic (de adaptat când se adaugă legătura)
        $stmt = db()->prepare('SELECT id_medic, nume, prenume, specializare, telefon FROM Medic WHERE id_medic = ?');
        $stmt->execute([$idUtilizator]);
        $row = $stmt->fetch();
        if ($row) $row['id'] = $row['id_medic'];
        return $row ?: null;
    }
    
    public static function all() {
        if (isMockMode()) {
            return array_values($GLOBALS['MOCK_MEDICI']);
        }
        $rows = db()->query('SELECT id_medic, nume, prenume, specializare, telefon FROM Medic ORDER BY nume, prenume')->fetchAll();
        foreach ($rows as &$r) { $r['id'] = $r['id_medic']; }
        return $rows;
    }
    
    public static function count() {
        if (isMockMode()) {
            return count($GLOBALS['MOCK_MEDICI']);
        }
        return (int)db()->query('SELECT COUNT(*) FROM Medic')->fetchColumn();
    }
    
    public static function fullName($medic) {
        if (!$medic) return '-';
        return 'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
    }
}
