<?php
class VersionHistoryRepo {
    
    /**
     * Salvează un snapshot înainte de modificare
     */
    public static function saveSnapshot($entitate, $entitateId, $actiune, $dateVechi, $dateNoi = null) {
        if (isMockMode()) return true;
        try {
            $userId = null;
            $user = currentUser();
            if ($user) $userId = $user['id_utilizator'] ?? null;
            
            $stmt = db()->prepare('INSERT INTO VersionHistory 
                (entitate, entitate_id, id_utilizator, actiune, date_vechi, date_noi)
                VALUES (?, ?, ?, ?, ?, ?)');
            return $stmt->execute([
                $entitate, $entitateId, $userId, $actiune,
                is_array($dateVechi) ? json_encode($dateVechi, JSON_UNESCAPED_UNICODE) : $dateVechi,
                is_array($dateNoi) ? json_encode($dateNoi, JSON_UNESCAPED_UNICODE) : $dateNoi,
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Returnează istoricul versiunilor pentru o entitate
     */
    public static function findByEntity($entitate, $entitateId) {
        if (isMockMode()) return [];
        try {
            $stmt = db()->prepare('SELECT v.*, u.email 
                FROM VersionHistory v 
                LEFT JOIN Utilizatori u ON v.id_utilizator = u.id 
                WHERE v.entitate = ? AND v.entitate_id = ? 
                ORDER BY v.created_at DESC');
            $stmt->execute([$entitate, $entitateId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    /**
     * Toate versiunile recente (pentru audit)
     */
    public static function recent($limit = 50) {
        if (isMockMode()) return [];
        try {
            $limit = (int)$limit;
            $stmt = db()->query("SELECT TOP {$limit} v.*, u.email 
                FROM VersionHistory v 
                LEFT JOIN Utilizatori u ON v.id_utilizator = u.id 
                ORDER BY v.created_at DESC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }
}