<?php
/**
 * Repository pentru audit log (jurnal acțiuni utilizatori)
 * 
 * Criterii EuroRec:
 * - GS002182.1, GS002184.1, GS002198.2 (jurnal imutabil)
 * 
 * Câmpuri: id, id_utilizator, action, entity, entity_id, details,
 *          ip_address, user_agent, timestamp
 */
class AuditRepo {
    
    /**
     * Inserează o intrare în jurnal (insert-only, niciodată update sau delete)
     */
    public static function insert($entry) {
        if (isMockMode()) {
            $newId = empty($GLOBALS['MOCK_AUDIT']) ? 1 : max(array_keys($GLOBALS['MOCK_AUDIT'])) + 1;
            $entry['id'] = $newId;
            $GLOBALS['MOCK_AUDIT'][$newId] = $entry;
            return $newId;
        }
        
        $stmt = db()->prepare('INSERT INTO AuditLog 
            (id_utilizator, action, entity, entity_id, details, ip_address, user_agent, timestamp)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $entry['id_utilizator'] ?? null,
            $entry['action'],
            $entry['entity'],
            $entry['entity_id'] ?? null,
            $entry['details'] ?? '',
            $entry['ip_address'] ?? '',
            $entry['user_agent'] ?? '',
            $entry['timestamp'] ?? date('Y-m-d H:i:s'),
        ]);
        return db()->lastInsertId();
    }
    
    /**
     * Toate intrările (cele mai recente primele)
     */
    public static function all($limit = 100) {
        if (isMockMode()) {
            $arr = array_values($GLOBALS['MOCK_AUDIT']);
            usort($arr, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
            return array_slice($arr, 0, $limit);
        }
        $stmt = db()->prepare("SELECT TOP {$limit} * FROM AuditLog ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Filtrare după utilizator
     */
    public static function findByUser($idUtilizator) {
        if (isMockMode()) {
            $arr = array_values(array_filter($GLOBALS['MOCK_AUDIT'], 
                fn($a) => $a['id_utilizator'] == $idUtilizator));
            usort($arr, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
            return $arr;
        }
        $stmt = db()->prepare('SELECT * FROM AuditLog WHERE id_utilizator = ? ORDER BY timestamp DESC');
        $stmt->execute([$idUtilizator]);
        return $stmt->fetchAll();
    }
    
    /**
     * Filtrare după entitate
     */
    public static function findByEntity($entity, $entityId = null) {
        if (isMockMode()) {
            $arr = array_filter($GLOBALS['MOCK_AUDIT'], function($a) use ($entity, $entityId) {
                if ($a['entity'] !== $entity) return false;
                if ($entityId !== null && $a['entity_id'] != $entityId) return false;
                return true;
            });
            $arr = array_values($arr);
            usort($arr, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
            return $arr;
        }
        $sql = 'SELECT * FROM AuditLog WHERE entity = ?';
        $params = [$entity];
        if ($entityId !== null) {
            $sql .= ' AND entity_id = ?';
            $params[] = $entityId;
        }
        $sql .= ' ORDER BY timestamp DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}