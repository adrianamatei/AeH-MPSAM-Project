<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $currentPass = $_POST['parola_curenta'] ?? '';
    $newPass = $_POST['parola_noua'] ?? '';
    $confirmPass = $_POST['parola_confirmare'] ?? '';
    
    if (empty($currentPass)) $errors[] = 'Introduceți parola curentă.';
    if (empty($newPass)) $errors[] = 'Introduceți parola nouă.';
    if (strlen($newPass) < 6) $errors[] = 'Parola nouă trebuie să aibă cel puțin 6 caractere.';
    if ($newPass !== $confirmPass) $errors[] = 'Confirmarea parolei nu se potrivește.';
    
    if (empty($errors)) {
        if (changePassword($currentPass, $newPass)) {
            flash('success', 'Parola a fost schimbată cu succes.');
            redirect(url('profil.php'));
        } else {
            $errors[] = 'Parola curentă este incorectă.';
        }
    }
    
    foreach ($errors as $err) flash('error', $err);
}

renderHeader('Profil', '');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Setări</div>
        <h1>Profilul meu</h1>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>👤 Date cont</h3></div>
    <div class="card-body">
        <dl class="dl-grid">
            <dt>Email</dt><dd><?= e($user['email']) ?></dd>
            <dt>Rol</dt><dd><span class="badge badge-primary"><?= e($user['rol']) ?></span></dd>
            <dt>Nume</dt><dd><?= e($user['nume_complet'] ?? '-') ?></dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>🔐 Schimbă parola</h3></div>
    <div class="card-body">
        <form method="POST" action="" autocomplete="off">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
            
            <div class="form-group">
                <label class="form-label">Parola curentă <span class="required">*</span></label>
                <input type="password" name="parola_curenta" class="form-control" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Parola nouă <span class="required">*</span></label>
                    <input type="password" name="parola_noua" class="form-control" required minlength="6">
                    <div class="form-help">Minim 6 caractere</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmă parola nouă <span class="required">*</span></label>
                    <input type="password" name="parola_confirmare" class="form-control" required minlength="6">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Schimbă parola</button>
                <a href="<?= url('index.php') ?>" class="btn">Renunță</a>
            </div>
        </form>
    </div>
</div>

<?php renderFooter(); ?>
