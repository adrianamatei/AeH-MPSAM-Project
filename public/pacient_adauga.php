<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    // Validare
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
    if ($cnp && PacientRepo::findByCnp($cnp)) {
        $errors['cnp'] = 'CNP deja existent în sistem';
    }
    
    $email = trim($_POST['email'] ?? '');
    if ($email && !isValidEmail($email)) {
        $errors['email'] = 'Adresă email invalidă';
    }
    
    if (empty($errors)) {
        $data = [
            'id_medic' => $idMedic,
            'id_utilizator' => null,  // Va fi creat ulterior dacă pacientul vrea cont
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
        
        $newId = PacientRepo::insert($data);
        
        if ($newId) {
            logCurrentUserAction('CREATE', 'Pacient', $newId, 
                'Adăugare pacient: ' . $data['nume'] . ' ' . $data['prenume']);
            flash('success', 'Pacient adăugat cu succes.');
            redirect(url('pacient_detalii.php?id=' . $newId));
        } else {
            flash('error', 'Eroare la adăugarea pacientului.');
        }
    }
}

renderHeader('Adaugă pacient', 'pacient_adauga');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('pacienti.php') ?>">Pacienți</a> / Adaugă
        </div>
        <h1>Adaugă pacient nou</h1>
    </div>
</div>

<form method="POST" action="" autocomplete="off">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <!-- Date demografice -->
    <div class="card">
        <div class="card-header">
            <h3>Date demografice</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nume <span class="required">*</span></label>
                    <input type="text" name="nume" class="form-control" 
                           value="<?= e(old('nume')) ?>" required>
                    <?php if (isset($errors['nume'])): ?>
                        <div class="form-error"><?= e($errors['nume']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Prenume <span class="required">*</span></label>
                    <input type="text" name="prenume" class="form-control" 
                           value="<?= e(old('prenume')) ?>" required>
                    <?php if (isset($errors['prenume'])): ?>
                        <div class="form-error"><?= e($errors['prenume']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CNP <span class="required">*</span></label>
                    <input type="text" name="cnp" class="form-control" 
                           value="<?= e(old('cnp')) ?>" required maxlength="13"
                           data-validate="cnp"
                           pattern="[1-9][0-9]{12}">
                    <?php if (isset($errors['cnp'])): ?>
                        <div class="form-error"><?= e($errors['cnp']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Vârstă <span class="required">*</span></label>
                    <input type="number" name="varsta" class="form-control" 
                           value="<?= e(old('varsta')) ?>" required min="0" max="120">
                    <?php if (isset($errors['varsta'])): ?>
                        <div class="form-error"><?= e($errors['varsta']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact -->
    <div class="card">
        <div class="card-header">
            <h3>Contact</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Stradă, număr</label>
                <input type="text" name="strada" class="form-control" 
                       value="<?= e(old('strada')) ?>"
                       placeholder="ex: Str. Trandafirilor nr. 12">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Oraș</label>
                    <input type="text" name="oras" class="form-control" 
                           value="<?= e(old('oras', 'Timișoara')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Județ</label>
                    <input type="text" name="judet" class="form-control" 
                           value="<?= e(old('judet', 'Timiș')) ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input type="tel" name="telefon" class="form-control" 
                           value="<?= e(old('telefon')) ?>"
                           placeholder="0712345678">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= e(old('email')) ?>"
                           placeholder="exemplu@email.ro">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Profesie</label>
                    <input type="text" name="profesie" class="form-control" 
                           value="<?= e(old('profesie')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Loc de muncă</label>
                    <input type="text" name="loc_de_munca" class="form-control" 
                           value="<?= e(old('loc_de_munca')) ?>">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Date medicale -->
    <div class="card">
        <div class="card-header">
            <h3>Date medicale</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Istoric medical</label>
                <textarea name="istoric_medical" class="form-control" rows="4"
                          placeholder="Boli cronice, intervenții chirurgicale, tratamente curente..."><?= e(old('istoric_medical')) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Alergii</label>
                <textarea name="alergii" class="form-control" rows="2"
                          placeholder="Medicamente, alimente, alți alergeni cunoscuți..."><?= e(old('alergii')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvează pacient</button>
        <a href="<?= url('pacienti.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>
