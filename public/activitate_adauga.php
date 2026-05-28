<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$idPacientPreset = (int)($_GET['id_pacient'] ?? 0);
$pacientiMedic = PacientRepo::findByMedic($idMedic);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $data = [
        'id_pacient' => (int)$_POST['id_pacient'],
        'nume_activitate' => trim($_POST['nume_activitate']),
        'descriere' => trim($_POST['descriere'] ?? ''),
        'data_programata' => $_POST['data_programata'],
        'ora_programata' => $_POST['ora_programata'],
        'este_finalizata' => 0,
    ];
    
    if ($data['id_pacient'] && medicCanAccessPacient($data['id_pacient']) && !empty($data['nume_activitate'])) {
        $newId = ActivitateRepo::insert($data);
        logCurrentUserAction('CREATE', 'Activitati', $newId);
        flash('success', 'Activitate programată cu succes.');
        redirect(url('activitati.php'));
    } else {
        flash('error', 'Completează toate câmpurile.');
    }
}

renderHeader('Activitate nouă', 'activitati');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?= url('activitati.php') ?>">Activități</a> / Nouă</div>
        <h1>Programare activitate nouă</h1>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pacient <span class="required">*</span></label>
                    <select name="id_pacient" class="form-control" required>
                        <option value="">— Selectează —</option>
                        <?php foreach ($pacientiMedic as $p): ?>
                            <option value="<?= e($p['id']) ?>" <?= selected($idPacientPreset, $p['id']) ?>>
                                <?= e(PacientRepo::fullName($p)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nume activitate <span class="required">*</span></label>
                    <input type="text" name="nume_activitate" class="form-control" required
                           value="<?= e(old('nume_activitate')) ?>"
                           placeholder="ex: Plimbare în parc">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data <span class="required">*</span></label>
                    <input type="date" name="data_programata" class="form-control" required
                           value="<?= e(old('data_programata', date('Y-m-d'))) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Ora <span class="required">*</span></label>
                    <input type="time" name="ora_programata" class="form-control" required
                           value="<?= e(old('ora_programata', '10:00')) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Descriere</label>
                <textarea name="descriere" class="form-control" rows="3"
                          placeholder="Detalii suplimentare, instrucțiuni..."><?= e(old('descriere')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Programează</button>
        <a href="<?= url('activitati.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>
