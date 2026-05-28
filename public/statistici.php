<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$pacienti = PacientRepo::findByMedic($idMedic);
$consultatii = ConsultatieRepo::findByMedic($idMedic);
$alarme = AlarmaRepo::findByMedic($idMedic);

// Distribuție pe vârste
$grupeVarsta = ['<60' => 0, '60-69' => 0, '70-79' => 0, '80+' => 0];
foreach ($pacienti as $p) {
    $v = (int)$p['varsta'];
    if ($v < 60) $grupeVarsta['<60']++;
    elseif ($v < 70) $grupeVarsta['60-69']++;
    elseif ($v < 80) $grupeVarsta['70-79']++;
    else $grupeVarsta['80+']++;
}

// Distribuție alarme pe tip
$tipuriAlarme = [];
foreach ($alarme as $a) {
    $tipuriAlarme[$a['tip_alarma']] = ($tipuriAlarme[$a['tip_alarma']] ?? 0) + 1;
}

// Consultații pe lună (ultimele 6 luni)
$consultatiiPerLuna = [];
for ($i = 5; $i >= 0; $i--) {
    $luna = date('Y-m', strtotime("-{$i} months"));
    $consultatiiPerLuna[$luna] = 0;
}
foreach ($consultatii as $c) {
    $luna = date('Y-m', strtotime($c['data_consultatie']));
    if (isset($consultatiiPerLuna[$luna])) {
        $consultatiiPerLuna[$luna]++;
    }
}

renderHeader('Statistici', 'statistici');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Rapoarte</div>
        <h1>Statistici medic</h1>
    </div>
    <div class="page-actions">
        <a href="<?= url('raport_statistici_pdf.php') ?>" class="btn">📄 Export PDF</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-label">Pacienți</div>
        <div class="stat-value"><?= count($pacienti) ?></div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Consultații</div>
        <div class="stat-value"><?= count($consultatii) ?></div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Alarme</div>
        <div class="stat-value"><?= count($alarme) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Vârstă medie</div>
        <div class="stat-value">
            <?= empty($pacienti) ? '—' : round(array_sum(array_column($pacienti, 'varsta')) / count($pacienti), 1) ?>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4);">
    
    <div class="card">
        <div class="card-header"><h3>Distribuție pe grupe de vârstă</h3></div>
        <div class="card-body"><canvas id="chartVarsta" height="200"></canvas></div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Tipuri alarme</h3></div>
        <div class="card-body"><canvas id="chartAlarme" height="200"></canvas></div>
    </div>
    
</div>

<div class="card">
    <div class="card-header"><h3>Consultații pe lună (ultimele 6 luni)</h3></div>
    <div class="card-body"><canvas id="chartLuni" height="80"></canvas></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartVarsta'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($grupeVarsta)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($grupeVarsta)) ?>,
            backgroundColor: ['#4CAF50', '#0D7C5F', '#0A6B51', '#085842']
        }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('chartAlarme'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_map('strtoupper', array_keys($tipuriAlarme))) ?>,
        datasets: [{
            data: <?= json_encode(array_values($tipuriAlarme)) ?>,
            backgroundColor: ['#c62828', '#f57c00', '#fbc02d', '#0277bd']
        }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('chartLuni'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($consultatiiPerLuna)) ?>,
        datasets: [{
            label: 'Consultații',
            data: <?= json_encode(array_values($consultatiiPerLuna)) ?>,
            backgroundColor: '#0D7C5F'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

<?php renderFooter(); ?>
