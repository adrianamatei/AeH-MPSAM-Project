<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);
if (!$pacient) { flash('error', 'Pacient negăsit.'); redirect(url('pacienti.php')); }
requireAccessToPacient($idPacient);

$active = MedicatieRepo::findActive($idPacient);
$istoric = MedicatieRepo::findIstoric($idPacient);
$isMedic = hasRole('medic');

renderHeader('Medicație: ' . PacientRepo::fullName($pacient), 'medicatie');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <?php if ($isMedic): ?><a href="<?= url('pacienti.php') ?>">Pacienți</a> / <?php endif; ?>
            <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a> / Medicație
        </div>
        <h1>💊 Medicație</h1>
    </div>
    <?php if ($isMedic): ?>
    <div class="page-actions">
        <a href="<?= url('medicatie_adauga.php?id_pacient=' . $idPacient) ?>" class="btn btn-primary">+ Prescrie medicament</a>
    </div>
    <?php endif; ?>
</div>

<!-- Alergii - afișate mereu conform EuroRec GS002582.2 -->
<?php if (!empty($pacient['alergii']) && $pacient['alergii'] !== 'Niciuna' && $pacient['alergii'] !== 'fara'): ?>
<div class="flash flash-error" style="margin-bottom: var(--sp-4);">
    ⚠ <strong>Alergii cunoscute:</strong> <?= e($pacient['alergii']) ?>
</div>
<?php endif; ?>

<!-- Medicație curentă -->
<div class="card">
    <div class="card-header">
        <h3>💚 Medicație curentă (<?= count($active) ?>)</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($active)): ?>
            <div class="empty-state"><p class="text-muted">Nicio medicație activă.</p></div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produs</th>
                        <th>Doză</th>
                        <th>Posologie</th>
                        <th>Din data</th>
                        <th>Ultima prescriere</th>
                        <?php if ($isMedic): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active as $m): ?>
                        <tr>
                            <td>
                                <strong><?= e($m['produs']) ?></strong>
                                <?php if ($m['forma_prezentare']): ?>
                                    <br><span class="text-small text-muted"><?= e($m['forma_prezentare']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($m['doza'] ?? '-') ?></td>
                            <td><?= e($m['posologie'] ?? '-') ?></td>
                            <td class="text-small"><?= e(formatDate($m['data_inceput'])) ?></td>
                            <td class="text-small"><?= e(formatDate($m['data_ultima_prescriere'])) ?></td>
                            <?php if ($isMedic): ?>
                            <td class="actions">
                                <a href="<?= url('medicatie_editare.php?id=' . $m['id']) ?>" class="btn btn-sm btn-outline">Editează</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Istoric medicație -->
<div class="card mt-4">
    <div class="card-header">
        <h3>📋 Istoric medicație (<?= count($istoric) ?>)</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($istoric)): ?>
            <div class="empty-state"><p class="text-muted">Niciun medicament în istoric.</p></div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Produs</th><th>Doză</th><th>Perioadă</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($istoric as $m): ?>
                        <tr style="opacity: 0.7;">
                            <td>
                                <?= e($m['produs']) ?>
                                <?php if ($m['forma_prezentare']): ?>
                                    <br><span class="text-small text-muted"><?= e($m['forma_prezentare']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($m['doza'] ?? '-') ?></td>
                            <td class="text-small">
                                <?= e(formatDate($m['data_inceput'])) ?> → 
                                <?= $m['data_sfarsit'] ? e(formatDate($m['data_sfarsit'])) : 'prezent' ?>
                            </td>
                            <td><span class="badge badge-secondary"><?= e(ucfirst($m['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>