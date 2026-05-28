<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();
$idPacientFilter = (int)($_GET['id_pacient'] ?? 0);

// Determină ce consultații afișează
if ($user['rol'] === 'medic') {
    $idMedic = currentMedicId();
    if ($idPacientFilter) {
        if (!medicCanAccessPacient($idPacientFilter)) {
            flash('error', 'Acces interzis.');
            redirect(url('pacienti.php'));
        }
        $consultatii = ConsultatieRepo::findByPacient($idPacientFilter);
        $pacientFiltrat = PacientRepo::findById($idPacientFilter);
    } else {
        $consultatii = ConsultatieRepo::findByMedic($idMedic);
        $pacientFiltrat = null;
    }
} else {
    $idPacient = currentPacientId();
    $consultatii = ConsultatieRepo::findByPacient($idPacient);
    $pacientFiltrat = PacientRepo::findById($idPacient);
}

renderHeader('Consultații', 'consultatii');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Activitate medicală</div>
        <h1>
            Consultații
            <?php if ($pacientFiltrat && $user['rol'] === 'medic'): ?>
                <span class="text-muted" style="font-size:1rem; font-weight:400;">
                    · <?= e(PacientRepo::fullName($pacientFiltrat)) ?>
                </span>
            <?php endif; ?>
        </h1>
    </div>
    <?php if ($user['rol'] === 'medic'): ?>
    <div class="page-actions">
        <a href="<?= url('consultatie_adauga.php' . ($idPacientFilter ? '?id_pacient=' . $idPacientFilter : '')) ?>" 
           class="btn btn-primary">+ Consultație nouă</a>
    </div>
    <?php endif; ?>
</div>

<?php if ($pacientFiltrat && $user['rol'] === 'medic'): ?>
    <div class="flash flash-info">
        Afișezi consultațiile pentru <strong><?= e(PacientRepo::fullName($pacientFiltrat)) ?></strong>.
        <a href="<?= url('consultatii.php') ?>">Vezi toate</a>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($consultatii)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>Nicio consultație înregistrată</h3>
                <?php if ($user['rol'] === 'medic'): ?>
                    <a href="<?= url('consultatie_adauga.php') ?>" class="btn btn-primary mt-3">+ Adaugă consultație</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th>Pacient</th>
                        <?php endif; ?>
                        <th>Motiv prezentare</th>
                        <th>Diagnostic (ICD-10)</th>
                        <th class="actions">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultatii as $c): 
                        $pacient = PacientRepo::findById($c['id_pacient']);
                    ?>
                        <tr>
                            <td class="text-small"><?= e(formatDate($c['data_consultatie'])) ?></td>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td>
                                    <a href="<?= url('pacient_detalii.php?id=' . $c['id_pacient']) ?>">
                                        <?= e(PacientRepo::fullName($pacient)) ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            <td><?= e(truncate($c['motiv_prezentare'], 50)) ?></td>
                            <td><?= e(truncate($c['diagnostic'], 60)) ?></td>
                            <td class="actions">
                                <a href="<?= url('consultatie_detalii.php?id=' . $c['id']) ?>" 
                                   class="btn btn-sm btn-outline">Vezi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php if (!empty($consultatii)): ?>
        <div class="card-footer text-small text-muted">
            Total: <?= count($consultatii) ?> consultații
        </div>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
