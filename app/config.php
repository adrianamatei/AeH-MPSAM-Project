<?php
/**
 * Configurație centrală Vital Cares
 * Toate fișierele includ doar acest fișier.
 */

// ── Sesiune ──
session_start();

// ── Constante aplicație ──
define('APP_NAME', 'Vital Cares');
define('APP_VERSION', '1.0.0');

// ── Sursa de date ──
// 'mock'  = date fictive din /data/mock_data.php
// 'azure' = Azure SQL Server (producție)
define('DATA_SOURCE', 'azure');

// ── Azure SQL Server (completează cu datele reale) ──
define('AZURE_SQL_HOST', 'vitalcares.database.windows.net');
define('AZURE_SQL_PORT', 1433);
define('AZURE_SQL_DATABASE', 'vitalcares');
define('AZURE_SQL_USER', 'CloudSAa5305cf1');
define('AZURE_SQL_PASSWORD', 'password123!');

// ── Securitate ──
define('SESSION_TIMEOUT', 30 * 60); // 30 min (EuroRec GS002655.2)
define('CSRF_TOKEN_NAME', '_token');

// ── Path-uri ──
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('DATA_PATH', BASE_PATH . '/data');
define('ASSETS_URL', '/assets');

// ── Auto-load: încarcă toate fișierele necesare ──
require_once APP_PATH . '/db.php';
require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/auth.php';
require_once APP_PATH . '/guards.php';
require_once APP_PATH . '/audit.php';
require_once APP_PATH . '/layout.php';

// Încarcă toate repositoriile
foreach (glob(APP_PATH . '/repositories/*.php') as $repoFile) {
    require_once $repoFile;
}

// Încarcă mock data DOAR dacă suntem pe mock
if (DATA_SOURCE === 'mock') {
    require_once DATA_PATH . '/mock_data.php';
}
