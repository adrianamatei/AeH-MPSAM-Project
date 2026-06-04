<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idPacient = (int)($_GET['id_pacient'] ?? 0);
$pacient = PacientRepo::findById($idPacient);
if (!$pacient) { flash('error', 'Pacient negăsit.'); redirect(url('pacienti.php')); }
requireAccessToPacient($idPacient);

$idMedic = currentMedicId();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $data = [
        'id_pacient' => $idPacient,
        'id_medic' => $idMedic,
        'id_consultatie' => $_POST['id_consultatie'] ?? null,
        'produs' => trim($_POST['produs'] ?? ''),
        'forma_prezentare' => trim($_POST['forma_prezentare'] ?? ''),
        'doza' => trim($_POST['doza'] ?? ''),
        'posologie' => trim($_POST['posologie'] ?? ''),
        'data_inceput' => $_POST['data_inceput'] ?? date('Y-m-d'),
        'data_sfarsit' => $_POST['data_sfarsit'] ?: null,
        'data_ultima_prescriere' => date('Y-m-d'),
        'status' => 'activ',
        'observatii' => trim($_POST['observatii'] ?? ''),
    ];
    
    if (empty($data['produs'])) $errors['produs'] = 'Numele produsului este obligatoriu.';
    if (empty($data['data_inceput'])) $errors['data_inceput'] = 'Data începerii este obligatorie.';
    
    if (empty($errors)) {
        $newId = MedicatieRepo::insert($data);
        if ($newId) {
            logCurrentUserAction('CREATE', 'Medicatie', $newId, 'Prescriere: ' . $data['produs'] . ' pentru ' . PacientRepo::fullName($pacient));
            flash('success', 'Medicament prescris cu succes.');
            redirect(url('medicatie.php?id=' . $idPacient));
        }
    }
}

renderHeader('Prescrie medicament', 'medicatie');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a> / 
            <a href="<?= url('medicatie.php?id=' . $idPacient) ?>">Medicație</a> / Prescrie
        </div>
        <h1>Prescrie medicament</h1>
    </div>
</div>

<!-- Alergii -->
<?php if (!empty($pacient['alergii']) && $pacient['alergii'] !== 'Niciuna' && $pacient['alergii'] !== 'fara'): ?>
<div class="flash flash-error">
    ⚠ <strong>Alergii cunoscute:</strong> <?= e($pacient['alergii']) ?>
</div>
<?php endif; ?>

<!-- Sumar pacient EuroRec GS002497.3 -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-center gap-3">
            <div class="patient-avatar"><?= e(mb_substr($pacient['nume'],0,1) . mb_substr($pacient['prenume'],0,1)) ?></div>
            <div>
                <strong><?= e(PacientRepo::fullName($pacient)) ?></strong>
                <div class="text-small text-muted">CNP: <?= e($pacient['cnp'] ?? $pacient['CNP'] ?? '') ?> · <?= e($pacient['varsta']) ?> ani · <?= e($pacient['sex'] ?? '') ?></div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="" autocomplete="off">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h3>💊 Detalii medicament</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Produs medicamentos <span class="required">*</span></label>
                    <input type="text" name="produs" class="form-control" required
                           value="<?= e(old('produs')) ?>"
                           placeholder="ex: Metoprolol, Amlodipină, Aspirină...">
                    <?php if (isset($errors['produs'])): ?><div class="form-error"><?= e($errors['produs']) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Formă de prezentare</label>
                    <select name="forma_prezentare" class="form-control">
                        <option value="">— Selectează —</option>
                        <option value="Comprimate" <?= old('forma_prezentare') === 'Comprimate' ? 'selected' : '' ?>>Comprimate</option>
                        <option value="Capsule" <?= old('forma_prezentare') === 'Capsule' ? 'selected' : '' ?>>Capsule</option>
                        <option value="Sirop" <?= old('forma_prezentare') === 'Sirop' ? 'selected' : '' ?>>Sirop</option>
                        <option value="Injecții" <?= old('forma_prezentare') === 'Injecții' ? 'selected' : '' ?>>Injecții</option>
                        <option value="Unguent" <?= old('forma_prezentare') === 'Unguent' ? 'selected' : '' ?>>Unguent</option>
                        <option value="Picături" <?= old('forma_prezentare') === 'Picături' ? 'selected' : '' ?>>Picături</option>
                        <option value="Supozitoare" <?= old('forma_prezentare') === 'Supozitoare' ? 'selected' : '' ?>>Supozitoare</option>
                        <option value="Plasture" <?= old('forma_prezentare') === 'Plasture' ? 'selected' : '' ?>>Plasture</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Doză</label>
                    <input type="text" name="doza" class="form-control"
                           value="<?= e(old('doza')) ?>"
                           placeholder="ex: 50mg, 100mg, 5ml...">
                </div>
                <div class="form-group">
                    <label class="form-label">Posologie (mod administrare)</label>
                    <input type="text" name="posologie" class="form-control"
                           value="<?= e(old('posologie')) ?>"
                           placeholder="ex: 1 comprimat de 2 ori pe zi, dimineața și seara">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data începerii tratamentului <span class="required">*</span></label>
                    <input type="date" name="data_inceput" class="form-control" required
                           value="<?= e(old('data_inceput', date('Y-m-d'))) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Data sfârșit (opțional)</label>
                    <input type="date" name="data_sfarsit" class="form-control"
                           value="<?= e(old('data_sfarsit')) ?>">
                    <div class="form-help">Lasă gol pentru tratament continuu</div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observații</label>
                <textarea name="observatii" class="form-control" rows="2"
                          placeholder="Indicații speciale, contraindicații..."><?= e(old('observatii')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">💊 Prescrie medicamentul</button>
        <a href="<?= url('medicatie.php?id=' . $idPacient) ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>