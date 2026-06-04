<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idAlarma = (int)($_GET['id'] ?? 0);
$alarma = AlarmaRepo::findById($idAlarma);

if (!$alarma) {
    flash('error', 'Alarmă negăsită.');
    redirect(url('alarme.php'));
}

if (!medicCanAccessPacient($alarma['id_pacient'])) {
    flash('error', 'Acces interzis.');
    redirect(url('alarme.php'));
}

// Arhivare
if (($_POST['action'] ?? '') === 'delete') {
    requireCsrf();
    AlarmaRepo::delete($idAlarma);
    logCurrentUserAction('ARCHIVE', 'Alarme', $idAlarma, 'Arhivare alarmă');
    flash('success', 'Alarma a fost arhivată cu succes.');
    flash('success', 'Alarmă ștearsă.');
    redirect(url('alarme.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    requireCsrf();
    
    $data = [
        'tip_alarma' => trim($_POST['tip_alarma'] ?? ''),
        'valoare_declansare' => (float)$_POST['valoare_declansare'],
        'prag_minim' => (float)$_POST['prag_minim'],
        'prag_maxim' => (float)$_POST['prag_maxim'],
        'durata_persistenta' => (int)$_POST['durata_persistenta'],
        'mesaj' => trim($_POST['mesaj'] ?? ''),
    ];
    
    if (AlarmaRepo::update($idAlarma, $data)) {
        logCurrentUserAction('UPDATE', 'Alarme', $idAlarma);
        flash('success', 'Alarmă actualizată.');
        redirect(url('alarme.php'));
    }
}

$pacient = PacientRepo::findById($alarma['id_pacient']);
$d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $alarma;

renderHeader('Editare alarmă', 'alarme');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?= url('alarme.php') ?>">Alarme</a> / Editare</div>
        <h1>Editare alarmă</h1>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="save">
    
    <div class="card">
        <div class="card-body">
            <div class="flash flash-info">
                Pacient: <strong><?= e(PacientRepo::fullName($pacient)) ?></strong> · 
                Declanșată: <?= e(formatDateTime($alarma['moment_declansare'])) ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tip alarmă</label>
                    <select name="tip_alarma" class="form-control" required>
                        <?php foreach (['puls', 'temperatura', 'ecg'] as $tip): ?>
                            <option value="<?= e($tip) ?>" <?= selected($d['tip_alarma'], $tip) ?>>
                                <?= e(strtoupper($tip)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Durată persistență (s)</label>
                    <input type="number" name="durata_persistenta" class="form-control" 
                           value="<?= e($d['durata_persistenta']) ?>" min="0">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prag minim</label>
                    <input type="number" name="prag_minim" class="form-control" 
                           value="<?= e($d['prag_minim']) ?>" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Prag maxim</label>
                    <input type="number" name="prag_maxim" class="form-control" 
                           value="<?= e($d['prag_maxim']) ?>" step="0.1">
                </div>
                <div class="form-group">
                    <label class="form-label">Valoare declanșare</label>
                    <input type="number" name="valoare_declansare" class="form-control" 
                           value="<?= e($d['valoare_declansare']) ?>" step="0.1">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mesaj</label>
                <textarea name="mesaj" class="form-control" rows="2"><?= e($d['mesaj']) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvează</button>
        <a href="<?= url('alarme.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<form method="POST" action="" style="margin-top: var(--sp-5);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="delete">
    <div class="card" style="border-color: var(--warning);">
        <div class="card-header" style="background: #fff8e8;">
            <h3 style="color: var(--warning);">📦 Arhivare alarmă</h3>
        </div>
        <div class="card-body">
            <p>Alarma va fi arhivată și nu va mai apărea în listele active. Poate fi restaurată din pagina de Arhivă.</p>
            <button type="submit" class="btn btn-danger" 
                    data-confirm="Ești sigur că vrei să arhivezi această alarmă?">
                📦 Arhivează alarma
            </button>
        </div>
    </div>
</form>

<?php renderFooter(); ?>