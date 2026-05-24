<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);

if (!$pacient) {
    flash('error', 'Pacient negăsit.');
    redirect(url('pacienti.php'));
}

// Verifică acces
if (!medicCanAccessPacient($idPacient)) {
    flash('error', 'Nu ai acces la acest pacient.');
    redirect(url('pacienti.php'));
}

$errors = [];

// Ștergere
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    requireCsrf();
    if (PacientRepo::delete($idPacient)) {
        logCurrentUserAction('DELETE', 'Pacient', $idPacient, 
            'Ștergere pacient: ' . PacientRepo::fullName($pacient));
        flash('success', 'Pacient șters cu succes.');
        redirect(url('pacienti.php'));
    } else {
        flash('error', 'Eroare la ștergere.');
    }
}

// Salvare modificări
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    requireCsrf();
    
    $required = ['nume', 'prenume', 'cnp', 'varsta'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[$field] = 'Câmp obligatoriu';
        }
    }
    
    $cnp = trim($_POST['cnp'] ?? '');
    if ($cnp && !isValidCNP($cnp)) {
        $errors['cnp'] = 'CNP invalid (13 cifre)';
    }
    // Verifică CNP duplicat (excluzând pacientul curent)
    $existent = PacientRepo::findByCnp($cnp);
    if ($existent && $existent['id'] != $idPacient) {
        $errors['cnp'] = 'CNP deja folosit pentru alt pacient';
    }
    
    $email = trim($_POST['email'] ?? '');
    if ($email && !isValidEmail($email)) {
        $errors['email'] = 'Adresă email invalidă';
    }
    
    if (empty($errors)) {
        $data = [
            'nume' => trim($_POST['nume']),
            'prenume' => trim($_POST['prenume']),
            'cnp' => $cnp,
            'varsta' => (int)$_POST['varsta'],
            'strada' => trim($_POST['strada'] ?? ''),
            'oras' => trim($_POST['oras'] ?? ''),
            'judet' => trim($_POST['judet'] ?? ''),
            'telefon' => trim($_POST['telefon'] ?? ''),
            'email' => $email,
            'profesie' => trim($_POST['profesie'] ?? ''),
            'loc_de_munca' => trim($_POST['loc_de_munca'] ?? ''),
            'istoric_medical' => trim($_POST['istoric_medical'] ?? ''),
            'alergii' => trim($_POST['alergii'] ?? ''),
        ];
        
        if (PacientRepo::update($idPacient, $data)) {
            logCurrentUserAction('UPDATE', 'Pacient', $idPacient, 
                'Editare date pacient: ' . $data['nume'] . ' ' . $data['prenume']);
            flash('success', 'Modificările au fost salvate.');
            redirect(url('pacient_detalii.php?id=' . $idPacient));
        } else {
            flash('error', 'Eroare la salvare.');
        }
    }
}

// Pentru repopulare folosește POST data dacă a fost trimis, altfel datele din DB
$d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $pacient;

renderHeader('Editare: ' . PacientRepo::fullName($pacient), 'pacienti');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('pacienti.php') ?>">Pacienți</a> / 
            <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a> /
            Editare
        </div>
        <h1>Editare pacient</h1>
    </div>
</div>

<form method="POST" action="" autocomplete="off">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="save">
    
    <div class="card">
        <div class="card-header"><h3>Date demografice</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nume <span class="required">*</span></label>
                    <input type="text" name="nume" class="form-control" 
                           value="<?= e($d['nume'] ?? '') ?>" required>
                    <?php if (isset($errors['nume'])): ?><div class="form-error"><?= e($errors['nume']) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Prenume <span class="required">*</span></label>
                    <input type="text" name="prenume" class="form-control" 
                           value="<?= e($d['prenume'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CNP <span class="required">*</span></label>
                    <input type="text" name="cnp" class="form-control" 
                           value="<?= e($d['cnp'] ?? '') ?>" required maxlength="13"
                           data-validate="cnp" pattern="[1-9][0-9]{12}">
                    <?php if (isset($errors['cnp'])): ?><div class="form-error"><?= e($errors['cnp']) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Vârstă <span class="required">*</span></label>
                    <input type="number" name="varsta" class="form-control" 
                           value="<?= e($d['varsta'] ?? '') ?>" required min="0" max="120">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Contact</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Stradă, număr</label>
                <input type="text" name="strada" class="form-control" value="<?= e($d['strada'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Oraș</label>
                    <input type="text" name="oras" class="form-control" value="<?= e($d['oras'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Județ</label>
                    <input type="text" name="judet" class="form-control" value="<?= e($d['judet'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input type="tel" name="telefon" class="form-control" value="<?= e($d['telefon'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($d['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?><div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Profesie</label>
                    <input type="text" name="profesie" class="form-control" value="<?= e($d['profesie'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Loc de muncă</label>
                    <input type="text" name="loc_de_munca" class="form-control" value="<?= e($d['loc_de_munca'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Date medicale</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Istoric medical</label>
                <textarea name="istoric_medical" class="form-control" rows="4"><?= e($d['istoric_medical'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Alergii</label>
                <textarea name="alergii" class="form-control" rows="2"><?= e($d['alergii'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions justify-between" style="display:flex; justify-content:space-between;">
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Salvează modificările</button>
            <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>" class="btn">Renunță</a>
        </div>
    </div>
</form>

<!-- Form separat pentru ștergere -->
<form method="POST" action="" style="margin-top: var(--sp-5);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="delete">
    <div class="card" style="border-color: var(--danger);">
        <div class="card-header" style="background: var(--danger-bg);">
            <h3 class="text-danger">⚠ Zona periculoasă</h3>
        </div>
        <div class="card-body">
            <p>Ștergerea pacientului va elimina toate datele asociate (consultații, alarme, măsurători). Această acțiune nu poate fi anulată.</p>
            <button type="submit" class="btn btn-danger" 
                    data-confirm="ATENȚIE: Ștergerea va elimina pacientul și toate datele asociate. Sigur continui?">
                🗑 Șterge pacientul
            </button>
        </div>
    </div>
</form>

<?php renderFooter(); ?>