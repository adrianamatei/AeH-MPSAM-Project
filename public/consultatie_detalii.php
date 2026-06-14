<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$idConsultatie = (int)($_GET['id'] ?? 0);
$consultatie = ConsultatieRepo::findById($idConsultatie);

if (!$consultatie) {
    flash('error', 'Consultație negăsită.');
    redirect(url('consultatii.php'));
}

requireAccessToPacient($consultatie['id_pacient']);

$pacient = PacientRepo::findById($consultatie['id_pacient']);
$medic = MedicRepo::findById($consultatie['id_medic']);

// Medicația legată de această consultație
$medicatieConsultatie = [];
if (!isMockMode()) {
    try {
        $stmt = db()->prepare('SELECT * FROM Medicatie WHERE id_consultatie = ? AND is_deleted = 0');
        $stmt->execute([$idConsultatie]);
        $medicatieConsultatie = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $medicatieConsultatie = [];
    }
}

// Arhivare consultație
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive' && hasRole('medic')) {
    requireCsrf();
    if (ConsultatieRepo::delete($idConsultatie)) {
        logCurrentUserAction('ARCHIVE', 'Consultatii', $idConsultatie, 
            'Arhivare consultație din ' . ($consultatie['data_consultatie'] ?? ''));
        flash('success', 'Consultația a fost arhivată cu succes.');
        redirect(url('consultatii.php'));
    } else {
        flash('error', 'Eroare la arhivare.');
    }
}

renderHeader('Consultație', 'consultatii');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('consultatii.php') ?>">Consultații</a> / 
            <?= e(formatDate($consultatie['data_consultatie'])) ?>
        </div>
        <h1>Consultație medicală</h1>
    </div>
    <?php if (hasRole('medic')): ?>
    <div class="page-actions">
        <a href="<?= url('medicatie_adauga.php?id_pacient=' . $pacient['id'] . '&id_consultatie=' . $idConsultatie) ?>" class="btn btn-primary">Prescrie medicație</a>
    </div>
    <?php endif; ?>
</div>

<!-- Sumar -->
<div class="card">
    <div class="card-body">
        <dl class="dl-grid">
            <dt>Data & ora</dt>
            <dd class="text-bold"><?= e(formatDateTime($consultatie['data_consultatie'])) ?></dd>
            
            <dt>Pacient</dt>
            <dd>
                <a href="<?= url('pacient_detalii.php?id=' . $pacient['id']) ?>" class="text-bold">
                    <?= e(PacientRepo::fullName($pacient)) ?>
                </a>
                · CNP <?= e($pacient['cnp'] ?? $pacient['CNP'] ?? '') ?> · <?= e($pacient['varsta']) ?> ani
            </dd>
            
            <dt>Medic</dt>
            <dd><?= e(MedicRepo::fullName($medic)) ?> · <?= e($medic['specializare']) ?></dd>
        </dl>
    </div>
</div>

<!-- Alergii pacient -->
<?php if (!empty($pacient['alergii']) && $pacient['alergii'] !== 'Niciuna' && $pacient['alergii'] !== 'Niciuna cunoscută'): ?>
<div class="card" style="border-color: var(--danger);">
    <div class="card-header" style="background: var(--danger-bg);">
        <h3 class="text-danger">Alergii cunoscute</h3>
    </div>
    <div class="card-body">
        <p class="text-danger text-bold"><?= e($pacient['alergii']) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Motivul prezentării -->
<div class="card">
    <div class="card-header"><h3>Motiv prezentare</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['motiv_prezentare']) ?></p>
    </div>
</div>

<!-- Simptome -->
<?php if (!empty($consultatie['simptome'])): ?>
<div class="card">
    <div class="card-header"><h3>Simptome</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['simptome']) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Diagnostic -->
<div class="card">
    <div class="card-header"><h3>Diagnostic</h3></div>
    <div class="card-body">
        <p class="text-bold" style="white-space:pre-wrap;"><?= e($consultatie['diagnostic']) ?></p>
    </div>
</div>

<!-- Medicație prescrisă în cadrul consultației -->
<div class="card">
    <div class="card-header">
        <h3>Medicație prescrisă</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($medicatieConsultatie)): ?>
            <div class="empty-state" style="padding: 24px;">
                <p class="text-muted">Nu a fost prescrisă medicație în cadrul acestei consultații.</p>
                <?php if (hasRole('medic')): ?>
                    <a href="<?= url('medicatie_adauga.php?id_pacient=' . $pacient['id'] . '&id_consultatie=' . $idConsultatie) ?>" class="btn btn-primary" style="margin-top: 8px;">Prescrie medicație</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produs</th>
                        <th>Doză</th>
                        <th>Posologie</th>
                        <th>Data început</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicatieConsultatie as $m): ?>
                        <tr>
                            <td class="text-bold">
                                <?= e($m['produs']) ?>
                                <?php if (!empty($m['forma_prezentare'])): ?>
                                    <br><span class="text-small text-muted"><?= e($m['forma_prezentare']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($m['doza'] ?? '-') ?></td>
                            <td><?= e($m['posologie'] ?? '-') ?></td>
                            <td class="text-small"><?= e(formatDate($m['data_inceput'])) ?></td>
                            <td>
                                <?php if ($m['status'] === 'activ'): ?>
                                    <span class="badge badge-success">Activ</span>
                                <?php elseif ($m['status'] === 'oprit'): ?>
                                    <span class="badge badge-danger">Oprit</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= e($m['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (hasRole('medic')): ?>
                <div style="padding: 12px 16px;">
                    <a href="<?= url('medicatie_adauga.php?id_pacient=' . $pacient['id'] . '&id_consultatie=' . $idConsultatie) ?>" class="btn btn-sm">+ Adaugă alt medicament</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Rețete (text liber, dacă există) -->
<?php if (!empty($consultatie['retete'])): ?>
<div class="card">
    <div class="card-header"><h3>Rețete / Observații tratament</h3></div>
    <div class="card-body">
        <pre style="white-space:pre-wrap; font-family:inherit;"><?= e($consultatie['retete']) ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Trimiteri -->
<?php if (!empty($consultatie['trimiteri'])): ?>
<div class="card">
    <div class="card-header"><h3>Trimiteri</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['trimiteri']) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="d-flex gap-2 mt-4">
    <a href="<?= url('consultatii.php') ?>" class="btn">Înapoi la listă</a>
    <a href="<?= url('pacient_detalii.php?id=' . $pacient['id']) ?>" class="btn">Vezi fișa pacient</a>
    <?php if (hasRole('medic')): ?>
        <a href="<?= url('consultatie_adauga.php?id_pacient=' . $pacient['id']) ?>" class="btn">Consultație nouă</a>
        <a href="<?= url('fhir_trimite.php?id_consultatie=' . $idConsultatie) ?>" class="btn">Trimite scrisoare FHIR</a>
    <?php endif; ?>
</div>

<?php if (hasRole('medic')): ?>
<form method="POST" action="" style="margin-top: var(--sp-5);">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="archive">
    <div class="card" style="border-color: var(--warning);">
        <div class="card-header" style="background: #fff8e8;">
            <h3 style="color: var(--warning);">Arhivare consultație</h3>
        </div>
        <div class="card-body">
            <p>Consultația va fi arhivată și nu va mai apărea în listele active. Datele sunt păstrate și pot fi restaurate ulterior.</p>
            <button type="submit" class="btn btn-danger" 
                    data-confirm="Ești sigur că vrei să arhivezi această consultație?">
                Arhivează consultația
            </button>
        </div>
    </div>
</form>
<?php endif; ?>

<?php renderFooter(); ?>