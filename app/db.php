<?php
/**
 * Gestionare conexiune la baza de date
 * 
 * Când DATA_SOURCE = 'mock', funcția db() returnează null și repositories
 * folosesc mock_data.php.
 * 
 * Când DATA_SOURCE = 'azure', funcția db() returnează o instanță PDO către
 * Azure SQL.
 */

/**
 * Returnează instanța PDO sau null (dacă suntem pe mock)
 * Folosește singleton pentru a reutiliza aceeași conexiune
 */
function db() {
    static $pdo = null;
    
    if (DATA_SOURCE === 'mock') {
        return null;
    }
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    // Conexiune SQL Server / Azure SQL prin driver-ul sqlsrv (PDO)
    // Necesită extensia PHP: php_pdo_sqlsrv
    $dsn = sprintf(
        'sqlsrv:Server=%s,%d;Database=%s;Encrypt=true;TrustServerCertificate=false',
        AZURE_SQL_HOST,
        AZURE_SQL_PORT,
        AZURE_SQL_DATABASE
    );
    
    try {
        $pdo = new PDO($dsn, AZURE_SQL_USER, AZURE_SQL_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $ex) {
        error_log('DB connection error: ' . $ex->getMessage());
        die('Eroare conexiune bază de date. Contactați administratorul.');
    }
    
    return $pdo;
}

/**
 * Verifică dacă suntem pe modul mock
 */
function isMockMode() {
    return DATA_SOURCE === 'mock';
}