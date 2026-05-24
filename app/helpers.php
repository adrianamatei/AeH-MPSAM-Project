<?php
/**
 * Funcții utilitare folosite în toată aplicația
 */

/**
 * Escape HTML pentru a preveni XSS
 * Folosește SIEMPRE când afișezi date utilizator: <?= e($var) ?>
 */
function e($value) {
    if ($value === null) return '';
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect către un URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Setează un mesaj flash (afișat la următoarea pagină)
 * @param string $type - 'success', 'error', 'warning', 'info'
 */
function flash($type, $message) {
    if (!isset($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Returnează și șterge toate mesajele flash
 */
function getFlash() {
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/**
 * Format dată românesc: 24.05.2026
 */
function formatDate($date) {
    if (!$date) return '-';
    try {
        $d = $date instanceof DateTime ? $date : new DateTime($date);
        return $d->format('d.m.Y');
    } catch (Exception $ex) {
        return '-';
    }
}

/**
 * Format dată + oră: 24.05.2026 14:30
 */
function formatDateTime($datetime) {
    if (!$datetime) return '-';
    try {
        $d = $datetime instanceof DateTime ? $datetime : new DateTime($datetime);
        return $d->format('d.m.Y H:i');
    } catch (Exception $ex) {
        return '-';
    }
}

/**
 * Generează token CSRF pentru forms
 * Folosire în form: <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
 */
function csrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verifică token-ul CSRF din POST
 * Folosire în pagini POST: if (!csrfVerify()) { ... }
 */
function csrfVerify() {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    return !empty($token) && hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

/**
 * Calculează vârsta din data nașterii
 */
function calculateAge($birthDate) {
    if (!$birthDate) return null;
    try {
        $birth = new DateTime($birthDate);
        $now = new DateTime('now');
        return $now->diff($birth)->y;
    } catch (Exception $ex) {
        return null;
    }
}

/**
 * Trunchiază text la N caractere
 */
function truncate($text, $length = 50) {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

/**
 * URL helper - construiește URL absolut bazat pe configurarea aplicației
 */
function url($path = '') {
    return '/' . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * Returnează valoarea din $_POST sau default (pentru repopulare form)
 */
function old($field, $default = '') {
    return $_POST[$field] ?? $default;
}

/**
 * Verifică dacă valoarea există în array (pentru selected/checked în forms)
 */
function selected($value, $expected) {
    return $value == $expected ? 'selected' : '';
}

function checked($value, $expected) {
    return $value == $expected ? 'checked' : '';
}

/**
 * Returnează clasa CSS activă pentru meniu
 */
function activeIf($currentPage, $menuItem, $class = 'active') {
    return $currentPage === $menuItem ? $class : '';
}

/**
 * Sanitizează input simplu (trim + filter)
 */
function sanitize($value) {
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

/**
 * Validare email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validare CNP românesc (basic - 13 cifre)
 */
function isValidCNP($cnp) {
    return preg_match('/^[1-9]\d{12}$/', $cnp) === 1;
}

/**
 * Format JSON response pentru API
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}