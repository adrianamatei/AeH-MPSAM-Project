<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();
$idPacientFilter = (int)($_GET['id_pacient'] ?? 0);

if ($user['rol'] === 'medic') {
    $idMedic = currentMedicId();
    if ($idPacientFilter) {
        if (!medicCanAccessPacient($idPacientFilter)) {
            flash('error', 'Acces interzis.'); redirect(url('alarme.php'));
        }
        $alarme = AlarmaRepo::findByPacient($idPacientFilter);
        $pacientFiltrat = PacientRepo::findById($idPacientFilter);
    } else {
        $alarme = AlarmaRepo::findByMedic($idMedic);
        $pacientFiltrat = null;
    }
} else {
    $idPacient = currentPacientId();
    $alarme = AlarmaRepo::findByPacient($idPacient);
    $pacientFiltrat = PacientRepo::findById($idPacient);
}

renderHeader('Alarme', 'alarme');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Monitorizare</div>
        <h1>
            Alarme și avertizări
            <?php if ($pacientFiltrat && $user['rol'] === 'medic'): ?>
                <span class="text-muted" style="font-size:1rem;"> · <?= e(PacientRepo::fullName($pacientFiltrat)) ?></span>
            <?php endif; ?>
        </h1>
    </div>
    <?php if ($user['rol'] === 'medic'): ?>
    <div class="page-actions">
        <a href="<?= url('alarma_adauga.php' . ($idPacientFilter ? '?id_pacient=' . $idPacientFilter : '')) ?>" 
           class="btn btn-primary">+ Alarmă nouă</a>
        <a href="<?= url('raport_alarme_pdf.php' . ($idPacientFilter ? '?id_pacient=' . $idPacientFilter : '')) ?>" 
           class="btn">📄 Export PDF</a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($alarme)): ?>
            <div class="empty-state">
                <div class="empty-icon">✓</div>
                <h3>Nicio alarmă</h3>
                <p>Toate valorile sunt în limite normale.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Moment</th>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th>Pacient</th>
                        <?php endif; ?>
                        <th>Tip alarmă</th>
                        <th>Valoare</th>
                        <th>Prag</th>
                        <th>Durată</th>
                        <th>Mesaj</th>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th class="actions">Acțiuni</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alarme as $a):
                        $pacient = PacientRepo::findById($a['id_pacient']);
                    ?>
                        <tr class="alarm-row">
                            <td class="text-small"><?= e(formatDateTime($a['moment_declansare'])) ?></td>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td>
                                    <a href="<?= url('pacient_detalii.php?id=' . $a['id_pacient']) ?>">
                                        <?= e(PacientRepo::fullName($pacient)) ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            <td><span class="badge badge-danger"><?= e(strtoupper($a['tip_alarma'])) ?></span></td>
                            <td class="vital-value danger"><?= e($a['valoare_declansare']) ?></td>
                            <td class="text-small text-muted"><?= e($a['prag_minim']) ?> - <?= e($a['prag_maxim']) ?></td>
                            <td class="text-small"><?= e($a['durata_persistenta']) ?>s</td>
                            <td><?= e(truncate($a['mesaj'], 50)) ?></td>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td class="actions">
                                    <a href="<?= url('alarma_editare.php?id=' . $a['id']) ?>" class="btn btn-sm">Editează</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php if (!empty($alarme)): ?>
        <div class="card-footer text-small text-muted">Total: <?= count($alarme) ?> alarme</div>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
