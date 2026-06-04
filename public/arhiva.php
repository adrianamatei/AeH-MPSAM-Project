<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$tab = $_GET['tab'] ?? 'pacienti';

// Restaurare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restore') {
    requireCsrf();
    $table = $_POST['table'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    
    $allowed = [
        'Pacient' => 'id',
        'Consultatii' => 'id',
        'Alarme' => 'id',
        'Recomandari' => 'id_recomandare',
        'Activitati' => 'id_activitate',
    ];
    
    if ($id && isset($allowed[$table]) && !isMockMode()) {
        $pk = $allowed[$table];
        $stmt = db()->prepare("UPDATE {$table} SET is_deleted = 0 WHERE {$pk} = ?");
        $stmt->execute([$id]);
        logCurrentUserAction('RESTORE', $table, $id, "Restaurare din arhivă: {$table} #{$id}");
        flash('success', 'Înregistrarea a fost restaurată cu succes.');
    }
    redirect(url('arhiva.php?tab=' . ($_POST['redirect_tab'] ?? 'pacienti')));
}

// Încarcă datele arhivate
$arhivaPacienti = [];
$arhivaConsultatii = [];
$arhivaAlarme = [];
$arhivaRecomandari = [];

if (!isMockMode()) {
    // Pacienți arhivați ai medicului curent
    $stmt = db()->prepare('SELECT *, CNP as cnp FROM Pacient WHERE id_medic = ? AND is_deleted = 1 ORDER BY nume, prenume');
    $stmt->execute([$idMedic]);
    $arhivaPacienti = $stmt->fetchAll();
    
    // Consultații arhivate
    $stmt = db()->prepare('SELECT c.*, p.nume as pacient_nume, p.prenume as pacient_prenume 
        FROM Consultatii c 
        INNER JOIN Pacient p ON c.id_pacient = p.id 
        WHERE c.id_medic = ? AND c.is_deleted = 1 
        ORDER BY c.data_consultatie DESC');
    $stmt->execute([$idMedic]);
    $arhivaConsultatii = $stmt->fetchAll();
    
    // Alarme arhivate
    $stmt = db()->prepare('SELECT a.*, p.nume as pacient_nume, p.prenume as pacient_prenume 
        FROM Alarme a 
        INNER JOIN Pacient p ON a.id_pacient = p.id 
        WHERE p.id_medic = ? AND a.is_deleted = 1 
        ORDER BY a.moment_declansare DESC');
    $stmt->execute([$idMedic]);
    $arhivaAlarme = $stmt->fetchAll();
    
    // Recomandări arhivate
    $stmt = db()->prepare('SELECT r.*, p.nume as pacient_nume, p.prenume as pacient_prenume 
        FROM Recomandari r 
        INNER JOIN Pacient p ON r.id_pacient = p.id 
        WHERE r.id_medic = ? AND r.is_deleted = 1 
        ORDER BY r.id_recomandare DESC');
    $stmt->execute([$idMedic]);
    $arhivaRecomandari = $stmt->fetchAll();
}

$totalArhivate = count($arhivaPacienti) + count($arhivaConsultatii) + count($arhivaAlarme) + count($arhivaRecomandari);

renderHeader('Arhivă', 'arhiva');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Administrare</div>
        <h1>📦 Arhivă (<?= $totalArhivate ?> înregistrări)</h1>
    </div>
</div>

<div class="tabs" style="margin-bottom: var(--sp-6);">
    <a href="?tab=pacienti" class="tab <?= $tab === 'pacienti' ? 'active' : '' ?>">
        👤 Pacienți (<?= count($arhivaPacienti) ?>)
    </a>
    <a href="?tab=consultatii" class="tab <?= $tab === 'consultatii' ? 'active' : '' ?>">
        📋 Consultații (<?= count($arhivaConsultatii) ?>)
    </a>
    <a href="?tab=alarme" class="tab <?= $tab === 'alarme' ? 'active' : '' ?>">
        ⚠ Alarme (<?= count($arhivaAlarme) ?>)
    </a>
    <a href="?tab=recomandari" class="tab <?= $tab === 'recomandari' ? 'active' : '' ?>">
        💊 Recomandări (<?= count($arhivaRecomandari) ?>)
    </a>
</div>

<?php if ($totalArhivate === 0): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Arhiva este goală</h3>
            <p>Nu există înregistrări arhivate.</p>
        </div>
    </div>
<?php endif; ?>

<!-- TAB: Pacienți arhivați -->
<?php if ($tab === 'pacienti'): ?>
    <div class="card">
        <div class="card-header"><h3>👤 Pacienți arhivați</h3></div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($arhivaPacienti)): ?>
                <div class="empty-state"><p class="text-muted">Niciun pacient arhivat.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Pacient</th><th>CNP</th><th>Vârstă</th><th>Telefon</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($arhivaPacienti as $p): ?>
                            <tr>
                                <td class="text-bold"><?= e($p['nume'] . ' ' . $p['prenume']) ?></td>
                                <td><?= e($p['cnp'] ?? $p['CNP'] ?? '') ?></td>
                                <td><?= e($p['varsta']) ?> ani</td>
                                <td><?= e($p['telefon']) ?></td>
                                <td class="actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="table" value="Pacient">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="redirect_tab" value="pacienti">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                                data-confirm="Restaurezi pacientul <?= e($p['nume'] . ' ' . $p['prenume']) ?>?">
                                            ♻ Restaurează
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- TAB: Consultații arhivate -->
<?php if ($tab === 'consultatii'): ?>
    <div class="card">
        <div class="card-header"><h3>📋 Consultații arhivate</h3></div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($arhivaConsultatii)): ?>
                <div class="empty-state"><p class="text-muted">Nicio consultație arhivată.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Data</th><th>Pacient</th><th>Diagnostic</th><th>Motiv</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($arhivaConsultatii as $c): ?>
                            <tr>
                                <td><?= e(formatDate($c['data_consultatie'])) ?></td>
                                <td class="text-bold"><?= e($c['pacient_nume'] . ' ' . $c['pacient_prenume']) ?></td>
                                <td><?= e(truncate($c['diagnostic'] ?? '', 50)) ?></td>
                                <td><?= e(truncate($c['motiv_prezentare'] ?? '', 50)) ?></td>
                                <td class="actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="table" value="Consultatii">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="redirect_tab" value="consultatii">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                                data-confirm="Restaurezi consultația?">
                                            ♻ Restaurează
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- TAB: Alarme arhivate -->
<?php if ($tab === 'alarme'): ?>
    <div class="card">
        <div class="card-header"><h3>⚠ Alarme arhivate</h3></div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($arhivaAlarme)): ?>
                <div class="empty-state"><p class="text-muted">Nicio alarmă arhivată.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Pacient</th><th>Tip</th><th>Valoare</th><th>Moment</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($arhivaAlarme as $a): ?>
                            <tr>
                                <td class="text-bold"><?= e($a['pacient_nume'] . ' ' . $a['pacient_prenume']) ?></td>
                                <td><span class="badge badge-danger"><?= e(strtoupper($a['tip_alarma'])) ?></span></td>
                                <td><?= e($a['valoare_declansatoare'] ?? $a['valoare_declansare'] ?? '') ?></td>
                                <td class="text-small"><?= e(formatDateTime($a['moment_declansare'])) ?></td>
                                <td class="actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="table" value="Alarme">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <input type="hidden" name="redirect_tab" value="alarme">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                                data-confirm="Restaurezi alarma?">
                                            ♻ Restaurează
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- TAB: Recomandări arhivate -->
<?php if ($tab === 'recomandari'): ?>
    <div class="card">
        <div class="card-header"><h3>💊 Recomandări arhivate</h3></div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($arhivaRecomandari)): ?>
                <div class="empty-state"><p class="text-muted">Nicio recomandare arhivată.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Pacient</th><th>Tip</th><th>Indicații</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($arhivaRecomandari as $r): ?>
                            <tr>
                                <td class="text-bold"><?= e($r['pacient_nume'] . ' ' . $r['pacient_prenume']) ?></td>
                                <td><span class="badge badge-primary"><?= e($r['tip_recomandare']) ?></span></td>
                                <td><?= e(truncate($r['indicatii'] ?? '', 60)) ?></td>
                                <td class="actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="table" value="Recomandari">
                                        <input type="hidden" name="id" value="<?= $r['id_recomandare'] ?>">
                                        <input type="hidden" name="redirect_tab" value="recomandari">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                                data-confirm="Restaurezi recomandarea?">
                                            ♻ Restaurează
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php renderFooter(); ?>