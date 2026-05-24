<?php
require_once __DIR__ . '/../app/config.php';

// Dacă deja logat, redirect la index
if (isLoggedIn()) {
    redirect(url('index.php'));
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['parola'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Completează email și parolă.';
    } else {
        $user = login($email, $password);
        if ($user) {
            // Redirect dupa login (la pagina cerută inițial sau dashboard)
            $redirectTo = $_SESSION['_redirect_after_login'] ?? null;
            unset($_SESSION['_redirect_after_login']);
            
            if ($redirectTo) {
                redirect($redirectTo);
            }
            // Altfel către index (care va redirecta la dashboard-ul propriu)
            redirect(url('index.php'));
        } else {
            $error = 'Email sau parolă incorectă.';
            // Audit eșec autentificare
            logAction(null, 'LOGIN_FAILED', 'Utilizatori', null, 'Login eșuat pentru: ' . $email);
        }
    }
}

renderHeader('Autentificare', '');
?>

<div class="login-card">
    <div class="logo-section">
        <div class="logo-circle">♥</div>
        <h1>Vital Cares</h1>
        <p>Sistem de monitorizare a sănătății</p>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
    
    <?php renderFlash(); ?>
    
    <form method="POST" action="">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
        
        <div class="form-group">
            <label class="form-label" for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?= e($email) ?>" required autofocus
                   placeholder="exemplu@vitalcares.ro">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="parola">Parolă <span class="required">*</span></label>
            <input type="password" id="parola" name="parola" class="form-control" 
                   required placeholder="••••••••">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                Autentificare
            </button>
        </div>
    </form>
    
    <div class="mt-4 text-small text-muted text-center">
        <strong>Demo:</strong> popescu@vitalcares.ro / parola123
    </div>
</div>

<?php renderFooter(); ?>