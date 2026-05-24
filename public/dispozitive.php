<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$dispozitive = DispozitivRepo::findByMedic($idMedic);

renderHeader('Dispozitive', 'dispozitive');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Monitorizare</div>
        <h1>Dispozitive asociate</h1>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($dispozitive)): ?>
            <div class="empty-state">
                <div class="empty-icon">📱</div>
                <h3>Niciun dispozitiv înregistrat</h3>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pacient</th>
                        <th>Tip</th>
                        <th>Detalii</th>
                        <th>Stare</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispozitive as $d):
                        $pacient = PacientRepo::findById($d['id_pacient']);
                    ?>
                        <tr>
                            <td>
                                <a href="<?= url('pacient_detalii.php?id=' . $d['id_pacient']) ?>">
                                    <?= e(PacientRepo::fullName($pacient)) ?>
                                </a>
                            </td>
                            <td><span class="badge badge-primary"><?= e($d['tip_dispozitiv']) ?></span></td>
                            <td class="text-small"><?= e($d['detalii']) ?></td>
                            <td>
                                <?php if ($d['stare'] === 'activ'): ?>
                                    <span class="badge badge-success">● Activ</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">○ Inactiv</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="card-footer text-small text-muted">
        Total: <?= count($dispozitive) ?> dispozitive
    </div>
</div>

<?php renderFooter(); ?>