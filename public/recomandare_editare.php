<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$id = (int)($_GET['id'] ?? 0);
$recomandare = RecomandareRepo::findById($id);

if (!$recomandare) {
    flash('error', 'Recomandare negăsită.');
    redirect(url('recomandari.php'));
}

if (!medicCanAccessPacient($recomandare['id_pacient'])) {
    flash('error', 'Acces interzis.');
    redirect(url('recomandari.php'));
}

if (($_POST['action'] ?? '') === 'delete') {
    requireCsrf();
    RecomandareRepo::delete($id);
    logCurrentUserAction('ARCHIVE', 'Recomandari', $id, 'Arhivare recomandare');
    flash('success', 'Recomandare arhivată cu succes.');
    redirect(url('recomandari.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    requireCsrf();
    RecomandareRepo::update($id, [
        'tip_recomandare' => trim($_POST['tip_recomandare']),
        'indicatii' => trim($_POST['indicatii']),
    ]);
    logCurrentUserAction('UPDATE', 'Recomandari', $id);
    flash('success', 'Recomandare actualizată.');
    redirect(url('recomandari.php'));
}

$pacient = PacientRepo::findById($recomandare['id_pacient']);
$d = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $recomandare;

renderHeader('Editare recomandare', 'recomandari');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?= url('recomandari.php') ?>">Recomandări</a> / Editare</div>
        <h1>Editare recomandare</h1>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="save">
    
    <div class="card">
        <div class="card-body">
            <div class="flash flash-info">
                Pacient: <strong><?= e(PacientRepo::fullName($pacient)) ?></strong>
            </div>
            
            <div class="form-group">
                <label class="form-label">Tip activitate</label>
                <select name="tip_recomandare" class="form-control" required>
                    <?php foreach (['plimbare', 'bicicletă', 'alergat', 'exerciții fizice', 'înot', 'yoga', 'altele'] as $t): ?>
                        <option value="<?= e($t) ?>" <?= selected($d['tip_recomandare'], $t) ?>><?= e(ucfirst($t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Indicații</label>
                <textarea name="indicatii" class="form-control" rows="5" required><?= e($d['indicatii']) ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvează</button>
        <a href="<?= url('recomandari.php') ?>" class="btn">Renunță</a>
    </div>
</form>

<form method="POST" action="" style="margin-top: var(--sp-5);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="delete">
    <div class="card" style="border-color: var(--warning);">
        <div class="card-header" style="background: #fff8e8;">
            <h3 style="color: var(--warning);">📦 Arhivare recomandare</h3>
        </div>
        <div class="card-body">
            <p>Recomandarea va fi arhivată și nu va mai apărea în listele active. Poate fi restaurată din pagina de Arhivă.</p>
            <button type="submit" class="btn btn-danger" 
                    data-confirm="Ești sigur că vrei să arhivezi această recomandare?">
                📦 Arhivează recomandarea
            </button>
        </div>
    </div>
</form>

<?php renderFooter(); ?>