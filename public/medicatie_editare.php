<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$id = (int)($_GET['id'] ?? 0);
$med = MedicatieRepo::findById($id);
if (!$med) { flash('error', 'Medicament negăsit.'); redirect(url('pacienti.php')); }

$pacient = PacientRepo::findById($med['id_pacient']);
requireAccessToPacient($med['id_pacient']);

// Oprire tratament
if (($_POST['action'] ?? '') === 'stop') {
    requireCsrf();
    MedicatieRepo::opreste($id);
    logCurrentUserAction('UPDATE', 'Medicatie', $id, 'Oprire tratament: ' . $med['produs']);
    flash('success', 'Tratamentul a fost oprit.');
    redirect(url('medicatie.php?id=' . $med['id_pacient']));
}

// Arhivare
if (($_POST['action'] ?? '') === 'archive') {
    requireCsrf();
    MedicatieRepo::delete($id);
    logCurrentUserAction('ARCHIVE', 'Medicatie', $id, 'Arhivare medicament: ' . $med['produs']);
    flash('success', 'Medicamentul a fost arhivat.');
    redirect(url('medicatie.php?id=' . $med['id_pacient']));
}

// Salvare modificări
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    requireCsrf();
    $data = [
        'produs' => trim($_POST['produs'] ?? ''),
        'forma_prezentare' => trim($_POST['forma_prezentare'] ?? ''),
        'doza' => trim($_POST['doza'] ?? ''),
        'posologie' => trim($_POST['posologie'] ?? ''),
        'data_inceput' => $_POST['data_inceput'] ?? '',
        'data_sfarsit' => $_POST['data_sfarsit'] ?: null,
        'data_ultima_prescriere' => date('Y-m-d'),
        'status' => $_POST['status'] ?? 'activ',
        'observatii' => trim($_POST['observatii'] ?? ''),
    ];
    
    if (!empty($data['produs']) && !empty($data['data_inceput'])) {
        MedicatieRepo::update($id, $data);
        logCurrentUserAction('UPDATE', 'Medicatie', $id, 'Modificare: ' . $data['produs']);
        flash('success', 'Medicament actualizat.');
        redirect(url('medicatie.php?id=' . $med['id_pacient']));
    }
}

$d = $med; // alias pentru formular
renderHeader('Editare medicament', 'medicatie');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('pacient_detalii.php?id=' . $med['id_pacient']) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a> / 
            <a href="<?= url('medicatie.php?id=' . $med['id_pacient']) ?>">Medicație</a> / Editare
        </div>
        <h1>Editare: <?= e($med['produs']) ?></h1>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h3>💊 Detalii medicament</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Produs <span class="required">*</span></label>
                    <input type="text" name="produs" class="form-control" required value="<?= e($d['produs']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Formă de prezentare</label>
                    <select name="forma_prezentare" class="form-control">
                        <option value="">—</option>
                        <?php foreach (['Comprimate','Capsule','Sirop','Injecții','Unguent','Picături','Supozitoare','Plasture'] as $f): ?>
                            <option value="<?= $f ?>" <?= ($d['forma_prezentare'] ?? '') === $f ? 'selected' : '' ?>><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Doză</label>
                    <input type="text" name="doza" class="form-control" value="<?= e($d['doza'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Posologie</label>
                    <input type="text" name="posologie" class="form-control" value="<?= e($d['posologie'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data început <span class="required">*</span></label>
                    <input type="date" name="data_inceput" class="form-control" required value="<?= e($d['data_inceput']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Data sfârșit</label>
                    <input type="date" name="data_sfarsit" class="form-control" value="<?= e($d['data_sfarsit'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="activ" <?= $d['status'] === 'activ' ? 'selected' : '' ?>>Activ</option>
                        <option value="oprit" <?= $d['status'] === 'oprit' ? 'selected' : '' ?>>Oprit</option>
                        <option value="finalizat" <?= $d['status'] === 'finalizat' ? 'selected' : '' ?>>Finalizat</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Observații</label>
                    <input type="text" name="observatii" class="form-control" value="<?= e($d['observatii'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvează</button>
        <a href="<?= url('medicatie.php?id=' . $med['id_pacient']) ?>" class="btn">Renunță</a>
    </div>
</form>

<!-- Oprire rapidă tratament -->
<?php if ($med['status'] === 'activ'): ?>
<form method="POST" action="" style="margin-top: var(--sp-4);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="stop">
    <div class="card" style="border-color: var(--warning);">
        <div class="card-header" style="background: #fff8e8;">
            <h3 style="color: var(--warning);">⏹ Oprire tratament</h3>
        </div>
        <div class="card-body">
            <p>Tratamentul va fi marcat ca oprit și va trece în istoric.</p>
            <button type="submit" class="btn btn-warning" data-confirm="Oprești tratamentul cu <?= e($med['produs']) ?>?">⏹ Oprește tratamentul</button>
        </div>
    </div>
</form>
<?php endif; ?>

<!-- Arhivare -->
<form method="POST" action="" style="margin-top: var(--sp-4);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="archive">
    <div class="card" style="border-color: var(--warning);">
        <div class="card-header" style="background: #fff8e8;">
            <h3 style="color: var(--warning);">📦 Arhivare</h3>
        </div>
        <div class="card-body">
            <p>Medicamentul va fi arhivat. Poate fi restaurat din pagina de Arhivă.</p>
            <button type="submit" class="btn btn-danger" data-confirm="Arhivezi medicamentul?">📦 Arhivează</button>
        </div>
    </div>
</form>

<?php renderFooter(); ?>