<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$idPacientPreset = (int)($_GET['id_pacient'] ?? 0);
$errors = [];

// Lista pacienților medicului pentru dropdown
$pacientiMedic = PacientRepo::findByMedic($idMedic);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $idPacient = (int)($_POST['id_pacient'] ?? 0);
    if (!$idPacient) {
        $errors['id_pacient'] = 'Selectează un pacient.';
    } elseif (!medicCanAccessPacient($idPacient)) {
        $errors['id_pacient'] = 'Pacient invalid.';
    }
    
    $data = [
        'id_pacient' => $idPacient,
        'id_medic' => $idMedic,
        'data_consultatie' => trim($_POST['data_consultatie'] ?? date('Y-m-d H:i:s')),
        'motiv_prezentare' => trim($_POST['motiv_prezentare'] ?? ''),
        'simptome' => trim($_POST['simptome'] ?? ''),
        'diagnostic' => trim($_POST['diagnostic'] ?? ''),
        'retete' => trim($_POST['retete'] ?? ''),
        'trimiteri' => trim($_POST['trimiteri'] ?? ''),
        'id_recomandari' => null,
    ];
    
    if (empty($data['motiv_prezentare'])) $errors['motiv_prezentare'] = 'Câmp obligatoriu';
    if (empty($data['diagnostic'])) $errors['diagnostic'] = 'Câmp obligatoriu';
    
    if (empty($errors)) {
        $newId = ConsultatieRepo::insert($data);
        if ($newId) {
            logCurrentUserAction('CREATE', 'Consultatii', $newId, 
                'Adăugare consultație pacient #' . $idPacient);
            flash('success', 'Consultația a fost înregistrată.');
            redirect(url('consultatie_detalii.php?id=' . $newId));
        } else {
            flash('error', 'Eroare la salvare.');
        }
    }
}

renderHeader('Consultație nouă', 'consultatii');
renderFlash();

$icdCodes = $GLOBALS['MOCK_ICD10'] ?? [];
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('consultatii.php') ?>">Consultații</a> / Consultație nouă
        </div>
        <h1>Adaugă consultație</h1>
    </div>
</div>

<form method="POST" action="" autocomplete="off">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h3>Informații generale</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pacient <span class="required">*</span></label>
                    <select name="id_pacient" class="form-control" required>
                        <option value="">— Selectează —</option>
                        <?php foreach ($pacientiMedic as $p): 
                            $selected = ($idPacientPreset == $p['id'] || old('id_pacient') == $p['id']);
                        ?>
                            <option value="<?= e($p['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= e(PacientRepo::fullName($p)) ?> (CNP: <?= e($p['cnp']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['id_pacient'])): ?>
                        <div class="form-error"><?= e($errors['id_pacient']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Data și ora <span class="required">*</span></label>
                    <input type="datetime-local" name="data_consultatie" class="form-control" 
                           value="<?= e(old('data_consultatie', date('Y-m-d\TH:i'))) ?>" required>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Anamneză</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Motiv prezentare <span class="required">*</span></label>
                <textarea name="motiv_prezentare" class="form-control" rows="2" required
                          placeholder="ex: Control periodic, dispnee la efort..."><?= e(old('motiv_prezentare')) ?></textarea>
                <?php if (isset($errors['motiv_prezentare'])): ?>
                    <div class="form-error"><?= e($errors['motiv_prezentare']) ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Simptome</label>
                <textarea name="simptome" class="form-control" rows="3"
                          placeholder="Descrierea detaliată a simptomelor..."><?= e(old('simptome')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Diagnostic & Tratament</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Diagnostic (ICD-10) <span class="required">*</span></label>
                <input type="text" name="diagnostic" class="form-control" required
                       value="<?= e(old('diagnostic')) ?>"
                       list="icd10-list"
                       placeholder="ex: I10 - Hipertensiune esențială">
                <datalist id="icd10-list">
                    <?php foreach ($icdCodes as $code => $desc): ?>
                        <option value="<?= e($code) ?> - <?= e($desc) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="form-help">Selectează din listă sau introdu manual cod ICD-10</div>
                <?php if (isset($errors['diagnostic'])): ?>
                    <div class="form-error"><?= e($errors['diagnostic']) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label">Rețete / Tratament</label>
                <textarea name="retete" class="form-control" rows="4"
                          placeholder="Listă medicamente: nume, doză, frecvență..."><?= e(old('retete')) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Trimiteri</label>
                <textarea name="trimiteri" class="form-control" rows="2"
                          placeholder="Analize, alte consultații, proceduri..."><?= e(old('trimiteri')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">💾 Salvează consultația</button>
        <a href="<?= url('consultatii.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>