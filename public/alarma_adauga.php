<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$idPacientPreset = (int)($_GET['id_pacient'] ?? 0);
$errors = [];

$pacientiMedic = PacientRepo::findByMedic($idMedic);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $idPacient = (int)($_POST['id_pacient'] ?? 0);
    if (!$idPacient || !medicCanAccessPacient($idPacient)) {
        $errors[] = 'Pacient invalid.';
    }
    
    $data = [
        'id_pacient' => $idPacient,
        'tip_alarma' => trim($_POST['tip_alarma'] ?? ''),
        'valoare_declansare' => (float)($_POST['valoare_declansare'] ?? 0),
        'prag_minim' => (float)($_POST['prag_minim'] ?? 0),
        'prag_maxim' => (float)($_POST['prag_maxim'] ?? 0),
        'durata_persistenta' => (int)($_POST['durata_persistenta'] ?? 0),
        'mesaj' => trim($_POST['mesaj'] ?? ''),
        'moment_declansare' => date('Y-m-d H:i:s'),
    ];
    
    if (empty($data['tip_alarma'])) $errors[] = 'Selectează tipul alarmei.';
    
    if (empty($errors)) {
        $newId = AlarmaRepo::insert($data);
        if ($newId) {
            logCurrentUserAction('CREATE', 'Alarme', $newId, 'Alarmă tip: ' . $data['tip_alarma']);
            flash('success', 'Alarmă definită cu succes.');
            redirect(url('alarme.php'));
        }
    } else {
        foreach ($errors as $err) flash('error', $err);
    }
}

renderHeader('Alarmă nouă', 'alarme');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?= url('alarme.php') ?>">Alarme</a> / Nouă</div>
        <h1>Definire alarmă nouă</h1>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h3>Configurare alarmă</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pacient <span class="required">*</span></label>
                    <select name="id_pacient" class="form-control" required>
                        <option value="">— Selectează —</option>
                        <?php foreach ($pacientiMedic as $p): 
                            $selected = ($idPacientPreset == $p['id']);
                        ?>
                            <option value="<?= e($p['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= e(PacientRepo::fullName($p)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tip alarmă <span class="required">*</span></label>
                    <select name="tip_alarma" class="form-control" required>
                        <option value="">— Selectează —</option>
                        <option value="puls">Puls</option>
                        <option value="temperatura">Temperatură</option>
                        <option value="ecg">ECG</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prag minim</label>
                    <input type="number" name="prag_minim" class="form-control" 
                           value="<?= e(old('prag_minim')) ?>" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Prag maxim</label>
                    <input type="number" name="prag_maxim" class="form-control" 
                           value="<?= e(old('prag_maxim')) ?>" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Valoare declanșare</label>
                    <input type="number" name="valoare_declansare" class="form-control" 
                           value="<?= e(old('valoare_declansare')) ?>" step="0.1">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Durată persistență (secunde)</label>
                <input type="number" name="durata_persistenta" class="form-control" 
                       value="<?= e(old('durata_persistenta', '30')) ?>" min="0">
                <div class="form-help">Alarma se declanșează doar dacă valoarea persistă în afara pragurilor pentru această durată.</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mesaj asociat</label>
                <textarea name="mesaj" class="form-control" rows="2"
                          placeholder="ex: Pacientul a raportat efort fizic intens"><?= e(old('mesaj')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Salvează alarma</button>
        <a href="<?= url('alarme.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<?php renderFooter(); ?>