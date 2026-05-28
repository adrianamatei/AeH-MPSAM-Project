<?php
/**
 * Sistem de audit logging
 * 
 * Înregistrează toate acțiunile importante: login, logout, create, update, delete.
 * 
 * Criterii EuroRec acoperite:
 * - GS002182.1: Audit trail login/logout
 * - GS002184.1: Audit trail evenimente securitate
 * - GS002198.2: Audit trail imutabil (insert-only)
 * - GS001538.2: Identificare actor care a introdus datele
 */

/**
 * Loghează o acțiune în jurnalul de audit
 * 
 * @param int|null $userId - ID utilizator (null pentru acțiuni anonime/eșuate)
 * @param string $action - tip acțiune: LOGIN, LOGOUT, CREATE, UPDATE, DELETE, VIEW, CHANGE_PASSWORD
 * @param string $entity - entitatea afectată: Pacient, Consultatii, etc.
 * @param int|null $entityId - ID-ul entității afectate
 * @param string $details - detalii suplimentare (text liber)
 */
function logAction($userId, $action, $entity, $entityId = null, $details = '') {
    $entry = [
        'id_utilizator' => $userId,
        'action' => $action,
        'entity' => $entity,
        'entity_id' => $entityId,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s'),
    ];
    
    AuditRepo::insert($entry);
}

/**
 * Loghează acțiunea curentă pentru utilizatorul logat
 */
function logCurrentUserAction($action, $entity, $entityId = null, $details = '') {
    $user = currentUser();
    $userId = $user['id_utilizator'] ?? null;
    logAction($userId, $action, $entity, $entityId, $details);
}
