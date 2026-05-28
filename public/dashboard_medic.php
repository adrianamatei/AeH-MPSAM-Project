<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();

// Statistici pentru medicul curent
$totalPacienti = PacientRepo::count($idMedic);
$totalConsultatii = ConsultatieRepo::count($idMedic);
$alarmeMedic = AlarmaRepo::findByMedic($idMedic);
$totalAlarme = count($alarmeMedic);

// Alarme din ultimele 24h
$alarme24h = array_filter($alarmeMedic, function($a) {
    return strtotime($a['moment_declansare']) >= strtotime('-24 hours');
});
$nrAlarme24h = count($alarme24h);

// Pacienți cu alarme active (cu cel puțin o alarmă în ultimele 24h)
$pacientiCuAlarme = array_unique(array_column($alarme24h, 'id_pacient'));

// Date pentru tabele
$consultatiiRecente = ConsultatieRepo::recente(5, $idMedic);
$alarmeRecente = AlarmaRepo::recente(5, $idMedic);
$pacientiLista = PacientRepo::findByMedic($idMedic);

renderHeader('Dashboard', 'dashboard');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Principal</div>
        <h1>Dashboard medic</h1>
    </div>
    <div class="page-actions">
        <a href="<?= url('pacient_adauga.php') ?>" class="btn btn-primary">+ Pacient nou</a>
    </div>
</div>

<!-- STATISTICI CARDS -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-label">Pacienții mei</div>
        <div class="stat-value"><?= $totalPacienti ?></div>
        <div class="stat-change">activi în sistem</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-label">Consultații</div>
        <div class="stat-value"><?= $totalConsultatii ?></div>
        <div class="stat-change">total efectuate</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-label">Alarme</div>
        <div class="stat-value"><?= $totalAlarme ?></div>
        <div class="stat-change"><?= $nrAlarme24h ?> în ultimele 24h</div>
    </div>
    
    <div class="stat-card danger">
        <div class="stat-label">Pacienți cu alerte</div>
        <div class="stat-value"><?= count($pacientiCuAlarme) ?></div>
        <div class="stat-change">de urmărit astăzi</div>
    </div>
</div>

<!-- ALARME RECENTE -->
<div class="card">
    <div class="card-header">
        <h3>⚠ Alarme recente</h3>
        <a href="<?= url('alarme.php') ?>" class="btn btn-sm btn-outline">Vezi toate</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($alarmeRecente)): ?>
            <div class="empty-state">
                <div class="empty-icon">✓</div>
                <h3>Niciuna alarmă recentă</h3>
                <p>Toți pacienții tăi au valori în limite normale.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pacient</th>
                        <th>Tip alarmă</th>
                        <th>Valoare</th>
                        <th>Moment</th>
                        <th>Mesaj</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alarmeRecente as $alarm):
                        $pacient = PacientRepo::findById($alarm['id_pacient']);
                    ?>
                        <tr class="alarm-row">
                            <td>
                                <a href="<?= url('pacient_detalii.php?id=' . $alarm['id_pacient']) ?>">
                                    <?= e(PacientRepo::fullName($pacient)) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-danger"><?= e(strtoupper($alarm['tip_alarma'])) ?></span>
                            </td>
                            <td>
                                <span class="vital-value danger">
                                    <?= e($alarm['valoare_declansare']) ?>
                                </span>
                            </td>
                            <td><?= e(formatDateTime($alarm['moment_declansare'])) ?></td>
                            <td><?= e(truncate($alarm['mesaj'], 60)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- CONSULTAȚII RECENTE + PACIENȚI -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); margin-top: var(--sp-4);">

    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>📋 Consultații recente</h3>
            <a href="<?= url('consultatii.php') ?>" class="btn btn-sm btn-outline">Vezi toate</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($consultatiiRecente)): ?>
                <div class="empty-state">
                    <p>Nicio consultație înregistrată.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pacient</th>
                            <th>Diagnostic</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultatiiRecente as $c):
                            $pacient = PacientRepo::findById($c['id_pacient']);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= url('consultatie_detalii.php?id=' . $c['id']) ?>">
                                        <?= e(PacientRepo::fullName($pacient)) ?>
                                    </a>
                                </td>
                                <td><?= e(truncate($c['diagnostic'], 40)) ?></td>
                                <td class="text-small text-muted"><?= e(formatDate($c['data_consultatie'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>👤 Pacienții mei</h3>
            <a href="<?= url('pacienti.php') ?>" class="btn btn-sm btn-outline">Vezi toți</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($pacientiLista)): ?>
                <div class="empty-state">
                    <p>Nu ai pacienți alocați.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nume</th>
                            <th>Vârstă</th>
                            <th>Alerte</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pacientiLista, 0, 5) as $p):
                            $hasAlarm = in_array($p['id'], $pacientiCuAlarme);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= url('pacient_detalii.php?id=' . $p['id']) ?>">
                                        <?= e(PacientRepo::fullName($p)) ?>
                                    </a>
                                </td>
                                <td><?= e($p['varsta']) ?> ani</td>
                                <td>
                                    <?php if ($hasAlarm): ?>
                                        <span class="badge badge-danger">Alertă</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">OK</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php renderFooter(); ?>
