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
        'id_medic' => $idMedic,
        'tip_recomandare' => trim($_POST['tip_recomandare']),
        'indicatii' => trim($_POST['indicatii']),
    ];
    
    if ($data['id_pacient'] && medicCanAccessPacient($data['id_pacient']) 
        && !empty($data['tip_recomandare']) && !empty($data['indicatii'])) {
        $newId = RecomandareRepo::insert($data);
        logCurrentUserAction('CREATE', 'Recomandari', $newId);
        flash('success', 'Recomandare adăugată. Va fi trimisă către aplicația mobilă a pacientului.');
        redirect(url('recomandari.php'));
    } else {
        flash('error', 'Completează toate câmpurile obligatorii.');
    }
}

renderHeader('Recomandare nouă', 'recomandari');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?= url('recomandari.php') ?>">Recomandări</a> / Nouă</div>
        <h1>Recomandare nouă</h1>
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
                    <label class="form-label">Tip activitate <span class="required">*</span></label>
                    <select name="tip_recomandare" class="form-control" required>
                        <option value="">— Selectează —</option>
                        <option value="plimbare">Plimbare</option>
                        <option value="bicicletă">Bicicletă</option>
                        <option value="alergat">Alergat</option>
                        <option value="exerciții fizice">Exerciții fizice</option>
                        <option value="înot">Înot</option>
                        <option value="yoga">Yoga / Stretching</option>
                        <option value="altele">Altele</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Indicații detaliate <span class="required">*</span></label>
                <textarea name="indicatii" class="form-control" rows="5" required
                          placeholder="Durată zilnică, intensitate, momentul zilei, precauții..."><?= e(old('indicatii')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvează recomandarea</button>
        <a href="<?= url('recomandari.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>