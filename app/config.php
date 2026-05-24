<?php
/**
 * Configurația centrală a aplicației Vital Cares
 * 
 * IMPORTANT: Toate setările globale se află aici.
 * Pentru a comuta între mock data și Azure SQL, modifică DATA_SOURCE.
 */

// ============================================
// SURSA DE DATE
// ============================================
// 'mock' = folosește data/mock_data.php (dezvoltare)
// 'azure' = folosește Azure SQL prin PDO (producție)
define('DATA_SOURCE', 'mock');

// ============================================
// CONFIGURARE AZURE SQL (populate când vin de la Roxana)
// ============================================
define('AZURE_SQL_HOST', '');           // ex: server.database.windows.net
define('AZURE_SQL_DATABASE', '');       // numele bazei de date
define('AZURE_SQL_USER', '');
define('AZURE_SQL_PASSWORD', '');
define('AZURE_SQL_PORT', 1433);

// ============================================
// APLICAȚIE
// ============================================
define('APP_NAME', 'Vital Cares');
define('APP_VERSION', '1.0');
define('APP_TIMEZONE', 'Europe/Bucharest');

// ============================================
// SESIUNE & SECURITATE
// ============================================
define('SESSION_NAME', 'vital_cares_session');
define('SESSION_TIMEOUT', 1800); // 30 minute (criteriul EuroRec GS002655.2)
define('CSRF_TOKEN_NAME', '_csrf');

// ============================================
// PATHS (calculate automat)
// ============================================
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('DATA_PATH', BASE_PATH . '/data');
define('ASSETS_URL', '/assets'); // URL relativ pentru CSS/JS/imagini

// ============================================
// SETĂRI PHP
// ============================================
date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding('UTF-8');

// În dezvoltare arătăm erorile; în producție le ascundem
$isDev = (DATA_SOURCE === 'mock');
error_reporting(E_ALL);
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');

// ============================================
// PORNIRE SESIUNE
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false, // pe http local; pune true pe HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ============================================
// ÎNCĂRCARE FIȘIERE COMUNE
// ============================================
require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/db.php';
require_once APP_PATH . '/auth.php';
require_once APP_PATH . '/guards.php';
require_once APP_PATH . '/audit.php';
require_once APP_PATH . '/layout.php';

// Încarcă mock data dacă suntem pe modul mock
if (DATA_SOURCE === 'mock') {
    require_once DATA_PATH . '/mock_data.php';
}

// Încarcă toate repositories
foreach (glob(APP_PATH . '/repositories/*.php') as $repoFile) {
    require_once $repoFile;
}