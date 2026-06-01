<?php
require_once __DIR__ . '/../app/config.php';

if (isLoggedIn()) {
    redirect(url('index.php'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $parola = $_POST['parola'] ?? '';
    $parola_confirm = $_POST['parola_confirm'] ?? '';
    $rol = strtolower(trim($_POST['rol'] ?? ''));
    $nume = trim($_POST['nume'] ?? '');
    $prenume = trim($_POST['prenume'] ?? '');
    
    // Validări
    if (empty($email) || empty($parola) || empty($rol) || empty($nume) || empty($prenume)) {
        $error = 'Toate câmpurile sunt obligatorii.';
    } elseif (!isValidEmail($email)) {
        $error = 'Adresă email invalidă.';
    } elseif (strlen($parola) < 6) {
        $error = 'Parola trebuie să aibă minim 6 caractere.';
    } elseif ($parola !== $parola_confirm) {
        $error = 'Confirmarea parolei nu se potrivește.';
    } elseif (!in_array($rol, ['medic', 'pacient'])) {
        $error = 'Rol invalid.';
    } elseif (UtilizatorRepo::findByEmail($email)) {
        $error = 'Există deja un cont cu acest email.';
    } else {
        $hashedPassword = password_hash($parola, PASSWORD_BCRYPT);
        
        if (isMockMode()) {
            $error = 'Înregistrarea nu funcționează în modul mock.';
        } else {
            try {
                // 1. Creează utilizatorul
                $stmt = db()->prepare('INSERT INTO Utilizatori (email, parola, rol) VALUES (?, ?, ?)');
                $stmt->execute([$email, $hashedPassword, ucfirst($rol)]);
                $userId = (int)db()->lastInsertId();
                
                if ($rol === 'medic') {
                    // 2. Creează entrada în tabela Medic
                    $specializare = trim($_POST['specializare'] ?? 'Cardiologie');
                    $telefon = trim($_POST['telefon'] ?? '');
                    $stmt = db()->prepare('INSERT INTO Medic (nume, prenume, specializare, telefon, id_utilizator) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$nume, $prenume, $specializare, $telefon, $userId]);
                } elseif ($rol === 'pacient') {
                    // 2. Caută pacientul existent (creat de medic) după CNP
                    $cnp = trim($_POST['cnp'] ?? '');
                    $telefon = trim($_POST['telefon'] ?? '');
                    
                    $existingPacient = null;
                    if ($cnp) {
                        $stmtFind = db()->prepare('SELECT id FROM Pacient WHERE CNP = ?');
                        $stmtFind->execute([$cnp]);
                        $existingPacient = $stmtFind->fetch();
                    }
                    
                    if ($existingPacient) {
                        // Pacientul există (creat de medic) — doar legăm contul
                        $stmtUpdate = db()->prepare('UPDATE Pacient SET id_utilizator = ? WHERE id = ?');
                        $stmtUpdate->execute([$userId, $existingPacient['id']]);
                    } else {
                        // Pacient nou (fără medic asociat încă)
                        $stmt = db()->prepare('INSERT INTO Pacient (nume, prenume, varsta, CNP, telefon, id_utilizator) VALUES (?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$nume, $prenume, 0, $cnp, $telefon, $userId]);
                    }
                }
                
                logAction($userId, 'REGISTER', 'Utilizatori', $userId, 'Cont nou: ' . $email . ' (' . $rol . ')');
                $success = 'Contul a fost creat cu succes! Te poți autentifica acum.';
                
            } catch (\PDOException $e) {
                $error = 'Eroare la crearea contului: ' . $e->getMessage();
            }
        }
    }
}

renderHeader('Înregistrare', '');
?>

<div class="login-card">
    <div class="logo-section">
        <div class="logo-circle">♥</div>
        <h1>Vital Cares</h1>
        <p>Creează cont nou</p>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="flash flash-success">
            <?= e($success) ?>
            <br><a href="<?= url('login.php') ?>" style="color: var(--success); font-weight:700;">→ Mergi la autentificare</a>
        </div>
    <?php else: ?>
    
    <form method="POST" action="" autocomplete="off">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
        
        <div class="form-group">
            <label class="form-label">Tip cont <span class="required">*</span></label>
            <select name="rol" id="rol-select" class="form-control" required onchange="toggleFields()">
                <option value="">— Selectează —</option>
                <option value="medic" <?= (($_POST['rol'] ?? '') === 'medic') ? 'selected' : '' ?>>Medic</option>
                <option value="pacient" <?= (($_POST['rol'] ?? '') === 'pacient') ? 'selected' : '' ?>>Pacient</option>
            </select>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Nume <span class="required">*</span></label>
                <input type="text" name="nume" class="form-control" value="<?= e($_POST['nume'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prenume <span class="required">*</span></label>
                <input type="text" name="prenume" class="form-control" value="<?= e($_POST['prenume'] ?? '') ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required placeholder="exemplu@vitalcares.ro">
        </div>
        
        <div class="form-group">
            <label class="form-label">Specializare</label>
            <input type="text" name="specializare" class="form-control" value="<?= e($_POST['specializare'] ?? 'Cardiologie') ?>" placeholder="ex: Cardiologie">
        </div>
        
        <div id="cnp-group" class="form-group" style="display:none;">
            <label class="form-label">CNP <span class="required">*</span></label>
            <input type="text" name="cnp" class="form-control" value="<?= e($_POST['cnp'] ?? '') ?>" maxlength="13" placeholder="13 cifre">
        </div>
        
        <div class="form-group">
            <label class="form-label">Telefon</label>
            <input type="tel" name="telefon" class="form-control" value="<?= e($_POST['telefon'] ?? '') ?>" placeholder="07xxxxxxxx">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Parolă <span class="required">*</span></label>
                <input type="password" name="parola" class="form-control" required minlength="6" placeholder="Minim 6 caractere">
            </div>
            <div class="form-group">
                <label class="form-label">Confirmă parola <span class="required">*</span></label>
                <input type="password" name="parola_confirm" class="form-control" required minlength="6">
            </div>
        </div>
        
        <div class="form-actions" style="border:none; padding-top: var(--sp-4);">
            <button type="submit" class="btn btn-primary btn-block btn-lg">Creează contul</button>
        </div>
    </form>
    <?php endif; ?>
    
    <div class="mt-4 text-small text-muted text-center">
        Ai deja cont? <a href="<?= url('login.php') ?>">Autentifică-te</a>
    </div>
</div>

<script>
function toggleFields() {
    var rol = document.getElementById('rol-select').value;
    var specGroup = document.querySelector('[name="specializare"]').parentElement;
    var cnpGroup = document.getElementById('cnp-group');
    specGroup.style.display = (rol === 'medic') ? 'block' : 'none';
    cnpGroup.style.display = (rol === 'pacient') ? 'block' : 'none';
}
toggleFields();
</script>

<?php renderFooter(); ?>