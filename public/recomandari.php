<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();

if ($user['rol'] === 'medic') {
    $idMedic = currentMedicId();
    $recomandari = RecomandareRepo::findByMedic($idMedic);
} else {
    $idPacient = currentPacientId();
    $recomandari = RecomandareRepo::findByPacient($idPacient);
}

renderHeader('Recomandări', 'recomandari');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Activitate medicală</div>
        <h1>Recomandări medicale</h1>
    </div>
    <?php if ($user['rol'] === 'medic'): ?>
    <div class="page-actions">
        <a href="<?= url('recomandare_adauga.php') ?>" class="btn btn-primary">+ Recomandare nouă</a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($recomandari)): ?>
            <div class="empty-state">
                <div class="empty-icon">💊</div>
                <h3>Nicio recomandare</h3>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th>Pacient</th>
                        <?php endif; ?>
                        <th>Tip</th>
                        <th>Indicații</th>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th class="actions">Acțiuni</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recomandari as $r):
                        $pacient = PacientRepo::findById($r['id_pacient']);
                    ?>
                        <tr>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td>
                                    <a href="<?= url('pacient_detalii.php?id=' . $r['id_pacient']) ?>">
                                        <?= e(PacientRepo::fullName($pacient)) ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            <td><span class="badge badge-primary"><?= e(strtoupper($r['tip_recomandare'])) ?></span></td>
                            <td><?= e($r['indicatii']) ?></td>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td class="actions">
                                    <a href="<?= url('recomandare_editare.php?id=' . $r['id_recomandare']) ?>" 
                                       class="btn btn-sm">Editează</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>
