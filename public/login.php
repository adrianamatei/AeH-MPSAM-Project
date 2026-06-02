<?php
require_once __DIR__ . '/../app/config.php';

// Verificăm dacă utilizatorul a venit din link-ul de email cu cerere de delogare
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    
    // Păstrăm email-ul pacientului înainte să distrugem sesiunea ca să nu-l pierdem
    $savedEmail = $_GET['target_email'] ?? '';
    
    if (function_exists('logout')) {
        logout(); 
    } else {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }
    
    // Redirecționăm înapoi la login, dar transmitem doar email-ul pacientului în siguranță
    header("Location: login.php?prefill=" . urlencode($savedEmail));
    exit;
}

// Dacă utilizatorul este deja logat (și nu s-a cerut delogarea), redirect la index
if (isLoggedIn()) {
    redirect(url('index.php'));
}

$error = '';

// Verificăm dacă avem un email trimis din link pentru a-l pune automat în căsuță
$email = isset($_GET['prefill']) ? trim($_GET['prefill']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['parola'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Completează email și parolă.';
    } else {
        $user = login($email, $password);
        if ($user) {
            $redirectTo = $_SESSION['_redirect_after_login'] ?? null;
            unset($_SESSION['_redirect_after_login']);
            
            if ($redirectTo) {
                redirect($redirectTo);
            }
            redirect(url('index.php'));
        } else {
            $error = 'Email sau parolă incorectă.';
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
            <!-- Căsuța va fi completată automat cu email-ul pacientului primit din link -->
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
        <br><br>
        Nu ai cont? <a href="<?= url('register.php') ?>">Înregistrează-te</a>
    </div>
</div>

<?php renderFooter(); ?>