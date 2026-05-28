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
            usort($data, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
            return array_slice($data, 0, $limit);
        }
        // Dacă tabela AuditLog nu există în Azure, returnăm array gol
        try {
            $stmt = db()->prepare('SELECT TOP (?) * FROM AuditLog ORDER BY timestamp DESC');
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Tabela nu există încă
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
                (id_utilizator, action, entity, entity_id, details, ip_address, timestamp)
                VALUES (?, ?, ?, ?, ?, ?, ?)');
            return $stmt->execute([
                $data['id_utilizator'], $data['action'], $data['entity'],
                $data['entity_id'], $data['details'], $data['ip_address'], $data['timestamp'],
            ]);
        } catch (\PDOException $e) {
            // Tabela nu există — ignorăm silențios
            return false;
        }
    }
}
