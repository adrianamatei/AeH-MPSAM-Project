<?php
/**
 * Router PHP pentru Development Server
 * 
 * UTILIZARE:
 *   cd D:\Proiect_BDIS\www\AeH-MPSAM-Project
 *   php -S localhost:8000 router.php
 * 
 * Rutează:
 *   /assets/*    → fișiere statice din /assets/
 *   /*           → pagini PHP din /public/
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Servește fișiere statice din /assets/
if (preg_match('#^/assets/(.+)$#', $uri, $matches)) {
    $filePath = __DIR__ . '/assets/' . $matches[1];
    if (is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($filePath);
        return true;
    }
    http_response_code(404);
    echo "Asset not found: {$matches[1]}";
    return true;
}

// 2. Servește paginile PHP din /public/
$publicPath = __DIR__ . '/public' . $uri;

// Dacă URL-ul e "/" sau nu conține extensie, adaugă index.php
if ($uri === '/' || $uri === '') {
    $publicPath = __DIR__ . '/public/index.php';
}

if (is_file($publicPath)) {
    // Dacă e fișier PHP, include-l
    if (pathinfo($publicPath, PATHINFO_EXTENSION) === 'php') {
        chdir(__DIR__ . '/public');
        include $publicPath;
        return true;
    }
    // Fișier non-PHP din public/ (dacă ar exista vreunul)
    return false;
}

// 3. Fallback 404
http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 - Pagina nu a fost găsită</h1><p>Calea <code>{$uri}</code> nu există.</p><p><a href='/login.php'>Mergi la login</a></p></body></html>";
return true;
