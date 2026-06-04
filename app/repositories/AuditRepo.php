<?php
/**
 * Repository: Audit Log
 * NOTA: Azure NU are tabelă de audit — logăm doar local/în memorie
 * Dacă se adaugă o tabelă AuditLog în Azure, se activează query-urile
 */
class AuditRepo {
    
    public static function all($limit = 100) {
        if (isMockMode()) {
            $data = $GLOBALS['MOCK_AUDIT'] ?? [];
            usort($data, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));
            return array_slice($data, 0, $limit);
        }
        try {
            $limit = (int)$limit;
            $stmt = db()->query("SELECT TOP {$limit} a.*, u.email, u.rol 
                FROM AuditLog a 
                LEFT JOIN Utilizatori u ON a.id_utilizator = u.id 
                ORDER BY a.created_at DESC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }
    
    public static function insert($data) {
        if (isMockMode()) {
            $GLOBALS['MOCK_AUDIT'][] = $data;
            return true;
        }
        try {
            $stmt = db()->prepare('INSERT INTO AuditLog 
                (id_utilizator, actiune, entitate, entitate_id, detalii, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)');
            return $stmt->execute([
                $data['id_utilizator'], $data['action'], $data['entity'],
                $data['entity_id'], $data['details'], $data['ip_address'],
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }
}