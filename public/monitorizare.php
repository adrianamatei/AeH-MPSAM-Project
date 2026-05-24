<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();

// Determină ID pacient
if ($user['rol'] === 'pacient') {
    $idPacient = currentPacientId();
} else {
    $idPacient = (int)($_GET['id'] ?? 0);
    if (!$idPacient) {
        // Listează pacienții ca să aleagă
        $pacienti = PacientRepo::findByMedic(currentMedicId());
        renderHeader('Monitorizare', 'monitorizare');
        renderFlash();
        ?>
        <div class="page-header">
            <h1>Monitorizare pacienți</h1>
        </div>
        <div class="card">
            <div class="card-header"><h3>Selectează un pacient</h3></div>
            <div class="card-body" style="padding:0;">
                <?php if (empty($pacienti)): ?>
                    <div class="empty-state">Nu ai pacienți.</div>
                <?php else: ?>
                    <table class="table">
                        <thead><tr><th>Pacient</th><th>Ultimele valori</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($pacienti as $p): 
                            $ult = MasuratoriRepo::ultimeleValori($p['id']);
                        ?>
                            <tr>
                                <td><?= e(PacientRepo::fullName($p)) ?> (<?= e($p['varsta']) ?> ani)</td>
                                <td class="text-small">
                                    <?php foreach (['puls' => 'bpm', 'spo2' => '%', 'temperatura' => '°C'] as $tip => $u): ?>
                                        <?php if (isset($ult[$tip])): ?>
                                            <span class="badge badge-secondary"><?= e(strtoupper($tip)) ?>: <?= e($ult[$tip]['valoare']) ?><?= e($u) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?= url('monitorizare.php?id=' . $p['id']) ?>" 
                                       class="btn btn-sm btn-primary">Vezi monitorizare</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php renderFooter();
        exit;
    }
}

$pacient = PacientRepo::findById($idPacient);
if (!$pacient) {
    flash('error', 'Pacient negăsit.');
    redirect(url('monitorizare.php'));
}
requireAccessToPacient($idPacient);

// Date pentru grafice (ultimele 24h)
$startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endDate = date('Y-m-d H:i:s');

$pulsData = MasuratoriRepo::findByPacientInterval($idPacient, $startDate, $endDate, 'puls');
$spo2Data = MasuratoriRepo::findByPacientInterval($idPacient, $startDate, $endDate, 'spo2');
$tempData = MasuratoriRepo::findByPacientInterval($idPacient, $startDate, $endDate, 'temperatura');

$praguri = PraguriRepo::findByPacient($idPacient);
$ultimeleValori = MasuratoriRepo::ultimeleValori($idPacient);

// Helper - formatare date pentru Chart.js
function chartData($data) {
    return [
        'labels' => array_map(fn($d) => date('H:i', strtotime($d['moment_inregistrare'])), $data),
        'values' => array_map(fn($d) => (float)$d['valoare'], $data),
    ];
}
$pulsChart = chartData($pulsData);
$spo2Chart = chartData($spo2Data);
$tempChart = chartData($tempData);

renderHeader('Monitorizare: ' . PacientRepo::fullName($pacient), 'monitorizare');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('monitorizare.php') ?>">Monitorizare</a> / 
            <?= e(PacientRepo::fullName($pacient)) ?>
        </div>
        <h1>Monitorizare live</h1>
    </div>
    <div class="page-actions">
        <a href="<?= url('raport_monitorizare_pdf.php?id=' . $idPacient) ?>" class="btn">📄 Export PDF</a>
    </div>
</div>

<!-- Sumar pacient (criteriul EuroRec GS002497.3) -->
<div class="card">
    <div class="card-body">
        <div class="d-flex align-center gap-3">
            <div class="patient-avatar"><?= e(mb_substr($pacient['nume'], 0, 1) . mb_substr($pacient['prenume'], 0, 1)) ?></div>
            <div style="flex-grow:1;">
                <div class="text-bold"><?= e(PacientRepo::fullName($pacient)) ?></div>
                <div class="text-small text-muted">CNP <?= e($pacient['cnp']) ?> · <?= e($pacient['varsta']) ?> ani</div>
            </div>
            <div class="text-small text-muted text-right">
                Praguri: Puls <?= e($praguri['min_puls']) ?>-<?= e($praguri['max_puls']) ?> · 
                SpO₂ min <?= e($praguri['min_spo2']) ?>% · 
                Temp max <?= e($praguri['max_temp']) ?>°C
            </div>
        </div>
    </div>
</div>

<!-- Ultimele valori -->
<div class="vitals-grid">
    <?php 
    $iconuri = ['puls' => '❤', 'spo2' => '💨', 'temperatura' => '🌡'];
    $unitati = ['puls' => 'bpm', 'spo2' => '%', 'temperatura' => '°C'];
    $labels = ['puls' => 'Puls', 'spo2' => 'Saturație O₂', 'temperatura' => 'Temperatură'];
    foreach (['puls', 'spo2', 'temperatura'] as $tip): 
        $m = $ultimeleValori[$tip] ?? null;
    ?>
        <div class="vital-widget">
            <div class="vital-icon"><?= $iconuri[$tip] ?></div>
            <div class="vital-label"><?= $labels[$tip] ?></div>
            <div class="vital-number">
                <span class="vital-value normal"><?= $m ? e($m['valoare']) : '—' ?></span>
                <span class="vital-unit"><?= e($unitati[$tip]) ?></span>
            </div>
            <div class="vital-time">
                <?= $m ? e(formatDateTime($m['moment_inregistrare'])) : 'Fără date' ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Grafice -->
<div class="card">
    <div class="card-header"><h3>📈 Evoluție puls (ultimele 24h)</h3></div>
    <div class="card-body">
        <canvas id="chartPuls" height="80"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>📈 Evoluție SpO₂ (ultimele 24h)</h3></div>
    <div class="card-body">
        <canvas id="chartSpo2" height="80"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>📈 Evoluție temperatură (ultimele 24h)</h3></div>
    <div class="card-body">
        <canvas id="chartTemp" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const charts = {
    puls: {
        canvas: 'chartPuls',
        labels: <?= json_encode($pulsChart['labels']) ?>,
        values: <?= json_encode($pulsChart['values']) ?>,
        color: '#c62828',
        unit: 'bpm',
        min: <?= e($praguri['min_puls']) ?>,
        max: <?= e($praguri['max_puls']) ?>
    },
    spo2: {
        canvas: 'chartSpo2',
        labels: <?= json_encode($spo2Chart['labels']) ?>,
        values: <?= json_encode($spo2Chart['values']) ?>,
        color: '#1976d2',
        unit: '%',
        min: <?= e($praguri['min_spo2']) ?>,
        max: 100
    },
    temp: {
        canvas: 'chartTemp',
        labels: <?= json_encode($tempChart['labels']) ?>,
        values: <?= json_encode($tempChart['values']) ?>,
        color: '#f57c00',
        unit: '°C',
        min: 35,
        max: <?= e($praguri['max_temp']) ?>
    }
};

Object.values(charts).forEach(cfg => {
    new Chart(document.getElementById(cfg.canvas), {
        type: 'line',
        data: {
            labels: cfg.labels,
            datasets: [{
                label: cfg.unit,
                data: cfg.values,
                borderColor: cfg.color,
                backgroundColor: cfg.color + '20',
                fill: true,
                tension: 0.3,
                pointRadius: 1,
                pointHoverRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });
});
</script>

<?php renderFooter(); ?>