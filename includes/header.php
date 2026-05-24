<?php
/**
 * Header HTML pentru toate paginile
 * 
 * Folosit prin renderHeader($title, $activeMenu) din layout.php
 * Variabile disponibile: $GLOBALS['_page_title'], $GLOBALS['_active_menu']
 */
$_title = $GLOBALS['_page_title'] ?? APP_NAME;
$_user = currentUser();
$_isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vital Cares - Sistem de monitorizare a sănătății pacienților vârstnici">
    <title><?= e($_title) ?> | <?= e(APP_NAME) ?></title>
    
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
</head>
<body>

<?php if ($_isLoggedIn): ?>
<div class="app-layout">
    
    <!-- HEADER (top bar) -->
    <header class="app-header">
        <div class="logo">
            <div class="logo-icon">♥</div>
            <span>Vital Cares</span>
        </div>
        
        <div class="header-right">
            <div class="user-info">
                <span><?= e($_user['nume_complet'] ?? $_user['email']) ?></span>
                <span class="user-badge"><?= e($_user['rol']) ?></span>
            </div>
            <a href="<?= url('profil.php') ?>" class="logout-btn" title="Profil">
                Profil
            </a>
            <a href="<?= url('logout.php') ?>" class="logout-btn" title="Ieșire">
                Ieșire
            </a>
        </div>
    </header>
<?php else: ?>
    <!-- Pentru pagini fără login (ex: login.php) -->
    <main class="app-main-full">
<?php endif; ?>