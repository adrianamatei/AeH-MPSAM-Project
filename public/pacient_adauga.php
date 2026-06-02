<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$errors = [];
$mode = $_POST['mode'] ?? 'nou'; // 'existent' sau 'nou'

// Pacienți fără medic (pentru dropdown "selectează existent")
$pacientiDisponibili = [];
if (!isMockMode()) {
    $stmt = db()->prepare('SELECT id, nume, prenume, CNP as cnp, varsta FROM Pacient WHERE id_medic IS NULL ORDER BY nume, prenume');
    $stmt->execute();
    $pacientiDisponibili = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    if ($mode === 'existent') {
        // ═══ VARIANTA 1: Asociere pacient existent ═══
        $idPacientSelectat = (int)($_POST['id_pacient_existent'] ?? 0);
        if (!$idPacientSelectat) {
            $errors[] = 'Selectează un pacient din listă.';
        } else {
            $pacient = PacientRepo::findById($idPacientSelectat);
            if (!$pacient) {
                $errors[] = 'Pacientul selectat nu există.';
            } elseif (!empty($pacient['id_medic'])) {
                $errors[] = 'Pacientul este deja asociat unui medic.';
            } else {
                // Asociem pacientul cu medicul curent
                if (isMockMode()) {
                    $GLOBALS['MOCK_PACIENT'][$idPacientSelectat]['id_medic'] = $idMedic;
                } else {
                    $stmt = db()->prepare('UPDATE Pacient SET id_medic = ? WHERE id = ?');
                    $stmt->execute([$idMedic, $idPacientSelectat]);
                }
                logCurrentUserAction('UPDATE', 'Pacient', $idPacientSelectat, 
                    'Asociere pacient existent: ' . $pacient['nume'] . ' ' . $pacient['prenume']);
                flash('success', 'Pacientul a fost adăugat în lista ta cu succes.');
                redirect(url('pacient_detalii.php?id=' . $idPacientSelectat));
            }
        }
        
    } else {
        // ═══ VARIANTA 2: Adăugare pacient nou ═══
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
        
        $email = trim($_POST['email'] ?? '');
        if ($email && !isValidEmail($email)) {
            $errors['email'] = 'Adresă email invalidă';
        }
        
        // Verifică CNP duplicat
        $existingPacient = $cnp ? PacientRepo::findByCnp($cnp) : null;
        if ($existingPacient && $existingPacient['id_medic'] == $idMedic) {
            $errors['cnp'] = 'Acest pacient este deja în lista ta.';
        } elseif ($existingPacient && !empty($existingPacient['id_medic'])) {
            $errors['cnp'] = 'CNP deja asociat altui medic.';
        }
        
        // Verifică email duplicat în Utilizatori
        if ($email && !isMockMode()) {
            $existingUser = UtilizatorRepo::findByEmail($email);
            if ($existingUser) {
                $errors['email'] = 'Există deja un cont cu acest email.';
            }
        }
        
        if (empty($errors)) {
            $idUtilizatorNou = null;
            
            // Dacă email e completat, creăm automat cont de utilizator
            if ($email && !isMockMode()) {
                try {
                    $defaultPassword = password_hash('parola123', PASSWORD_BCRYPT);
                    $stmt = db()->prepare('INSERT INTO Utilizatori (email, parola, rol) VALUES (?, ?, ?)');
                    $stmt->execute([$email, $defaultPassword, 'Pacient']);
                    $idUtilizatorNou = (int)db()->lastInsertId();
                    
                    logAction($idUtilizatorNou, 'REGISTER', 'Utilizatori', $idUtilizatorNou, 
                        'Cont creat automat de medic pentru pacient');
                } catch (\PDOException $e) {
                    // Cont nu s-a putut crea — continuăm fără
                    $idUtilizatorNou = null;
                }
            }
            
            if ($existingPacient && empty($existingPacient['id_medic'])) {
                // Pacientul există dar fără medic — asociem
                $updateData = [
                    'id_medic' => $idMedic,
                    'nume' => trim($_POST['nume']),
                    'prenume' => trim($_POST['prenume']),
                    'varsta' => (int)$_POST['varsta'],
                    'sex' => trim($_POST['sex'] ?? ''),
                    'data_nasterii' => $_POST['data_nasterii'] ?? null,
                    'strada' => trim($_POST['strada'] ?? ''),
                    'oras' => trim($_POST['oras'] ?? ''),
                    'judet' => trim($_POST['judet'] ?? ''),
                    'telefon' => trim($_POST['telefon'] ?? ''),
                    'profesie' => trim($_POST['profesie'] ?? ''),
                    'loc_de_munca' => trim($_POST['loc_de_munca'] ?? ''),
                    'istoric_medical' => trim($_POST['istoric_medical'] ?? ''),
                    'alergii' => trim($_POST['alergii'] ?? ''),
                ];
                
                if (!isMockMode()) {
                    $sql = 'UPDATE Pacient SET id_medic=?, nume=?, prenume=?, varsta=?, sex=?, data_nasterii=?, strada=?, oras=?, judet=?, telefon=?, profesie=?, loc_de_munca=?, istoric_medical=?, alergii=?';
                    $params = [$idMedic, $updateData['nume'], $updateData['prenume'], $updateData['varsta'], $updateData['sex'], $updateData['data_nasterii'], $updateData['strada'], $updateData['oras'], $updateData['judet'], $updateData['telefon'], $updateData['profesie'], $updateData['loc_de_munca'], $updateData['istoric_medical'], $updateData['alergii']];
                    if ($idUtilizatorNou) { $sql .= ', id_utilizator=?, email=?'; $params[] = $idUtilizatorNou; $params[] = $email; }
                    $sql .= ' WHERE id=?'; $params[] = $existingPacient['id'];
                    db()->prepare($sql)->execute($params);
                }
                
                // Trimite email de notificare
                if ($email && $idUtilizatorNou) {
                    sendWelcomeEmail($email, trim($_POST['prenume']), 'parola123');
                }
                
                flash('success', 'Pacientul a fost asociat cu succes.' . ($idUtilizatorNou ? ' Contul a fost creat pe ' . $email : ''));
                redirect(url('pacient_detalii.php?id=' . $existingPacient['id']));
                
            } else {
                // Pacient complet nou
                $data = [
                    'id_medic' => $idMedic,
                    'id_utilizator' => $idUtilizatorNou,
                    'nume' => trim($_POST['nume']),
                    'prenume' => trim($_POST['prenume']),
                    'cnp' => $cnp,
                    'varsta' => (int)$_POST['varsta'],
                    'sex' => trim($_POST['sex'] ?? ''),
                    'data_nasterii' => $_POST['data_nasterii'] ?? null,
                    'email' => $email,
                    'strada' => trim($_POST['strada'] ?? ''),
                    'oras' => trim($_POST['oras'] ?? ''),
                    'judet' => trim($_POST['judet'] ?? ''),
                    'telefon' => trim($_POST['telefon'] ?? ''),
                    'profesie' => trim($_POST['profesie'] ?? ''),
                    'loc_de_munca' => trim($_POST['loc_de_munca'] ?? ''),
                    'istoric_medical' => trim($_POST['istoric_medical'] ?? ''),
                    'alergii' => trim($_POST['alergii'] ?? ''),
                ];
                
                $newId = PacientRepo::insert($data);
                
                if ($newId) {
                    // Trimite email de notificare
                    if ($email && $idUtilizatorNou) {
                        sendWelcomeEmail($email, $data['prenume'], 'parola123');
                    }
                    
                    logCurrentUserAction('CREATE', 'Pacient', $newId, 
                        'Adăugare pacient: ' . $data['nume'] . ' ' . $data['prenume'] . ($idUtilizatorNou ? ' (cont creat: ' . $email . ')' : ''));
                    flash('success', 'Pacient adăugat cu succes.' . ($idUtilizatorNou ? ' Contul a fost creat pe ' . $email . ' cu parola implicită.' : ''));
                    redirect(url('pacient_detalii.php?id=' . $newId));
                } else {
                    flash('error', 'Eroare la adăugarea pacientului.');
                }
            }
        }
    }
    
    if (!empty($errors) && is_array($errors)) {
        foreach ($errors as $key => $val) {
            if (is_string($key)) continue; // skip field-specific errors
            flash('error', $val);
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
        <h1>Adaugă pacient</h1>
    </div>
</div>

<!-- TABS: Selectează mod -->
<div class="tabs" style="margin-bottom: var(--sp-6);">
    <a href="#" class="tab active" data-tab="nou" onclick="switchTab('nou'); return false;">Utilizator nou</a>
    <a href="#" class="tab" data-tab="existent" onclick="switchTab('existent'); return false;">🔍 Utilizator existent</a>
</div>

<!-- ═══ TAB 1: SELECTARE PACIENT EXISTENT ═══ -->
<div id="tab-existent" style="display:none;">
    <form method="POST" action="">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
        <input type="hidden" name="mode" value="existent">
        
        <div class="card">
            <div class="card-header"><h3>Selectează un pacient care are deja cont</h3></div>
            <div class="card-body">
                <?php if (empty($pacientiDisponibili)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👤</div>
                        <h3>Niciun pacient disponibil</h3>
                        <p>Nu există pacienți înregistrați care să nu fie asociați unui medic.</p>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label">Pacient <span class="required">*</span></label>
                        <select name="id_pacient_existent" class="form-control" required>
                            <option value="">— Selectează pacient —</option>
                            <?php foreach ($pacientiDisponibili as $p): ?>
                                <option value="<?= e($p['id']) ?>">
                                    <?= e($p['nume'] . ' ' . $p['prenume']) ?> (CNP: <?= e($p['cnp'] ?? $p['CNP'] ?? '') ?>, <?= e($p['varsta']) ?> ani)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Pacienți care s-au înregistrat singuri și nu au încă un medic curant</div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">Adaugă în lista mea</button>
                        <a href="<?= url('pacienti.php') ?>" class="btn">Renunță</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- ═══ TAB 2: ADAUGĂ PACIENT NOU ═══ -->
<div id="tab-nou">
    <form method="POST" action="" autocomplete="off">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
        <input type="hidden" name="mode" value="nou">
        
        <!-- Date demografice -->
        <div class="card">
            <div class="card-header"><h3>Date demografice</h3></div>
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
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">CNP <span class="required">*</span></label>
                        <input type="text" name="cnp" class="form-control" 
                               value="<?= e(old('cnp')) ?>" required maxlength="13"
                               data-validate="cnp" pattern="[1-9][0-9]{12}">
                        <div class="form-help">Completează CNP-ul și câmpurile de mai jos se completează automat</div>
                        <?php if (isset($errors['cnp'])): ?>
                            <div class="form-error"><?= e($errors['cnp']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vârstă <span class="required">*</span></label>
                        <input type="number" name="varsta" class="form-control" 
                               value="<?= e(old('varsta')) ?>" required min="0" max="120">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sex</label>
                        <select name="sex" class="form-control">
                            <option value="">— Selectează —</option>
                            <option value="Masculin" <?= (old('sex') === 'Masculin') ? 'selected' : '' ?>>Masculin</option>
                            <option value="Feminin" <?= (old('sex') === 'Feminin') ? 'selected' : '' ?>>Feminin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data nașterii</label>
                        <input type="date" name="data_nasterii" class="form-control" 
                               value="<?= e(old('data_nasterii')) ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cont & Contact -->
        <div class="card">
            <div class="card-header"><h3>Cont & Contact</h3></div>
            <div class="card-body">
                <div class="flash flash-info" style="margin-bottom: var(--sp-4);">
                    💡 Dacă introduci un email, sistemul va crea automat un cont pentru pacient 
                    (parola implicită: <strong>parola123</strong>) și îi va trimite un email de notificare.
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email pacient (pentru creare cont automat)</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= e(old('email')) ?>"
                               placeholder="exemplu@email.ro">
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefon</label>
                        <input type="tel" name="telefon" class="form-control" 
                               value="<?= e(old('telefon')) ?>"
                               placeholder="0712345678">
                    </div>
                </div>
                
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
            <div class="card-header"><h3>Date medicale</h3></div>
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
            <button type="submit" class="btn btn-primary btn-lg">💾 Salvează pacient</button>
            <a href="<?= url('pacienti.php') ?>" class="btn">Renunță</a>
        </div>
    </form>
</div>

<script>
function switchTab(tab) {
    document.getElementById('tab-nou').style.display = (tab === 'nou') ? 'block' : 'none';
    document.getElementById('tab-existent').style.display = (tab === 'existent') ? 'block' : 'none';
    document.querySelectorAll('.tab').forEach(function(t) {
        t.classList.toggle('active', t.getAttribute('data-tab') === tab);
    });
}
</script>

<?php renderFooter(); ?>