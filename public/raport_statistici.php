<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$medic = MedicRepo::findById($idMedic);
$pacienti = PacientRepo::findByMedic($idMedic);
$consultatii = ConsultatieRepo::findByMedic($idMedic);
$alarme = AlarmaRepo::findByMedic($idMedic);

// Vârstă medie
$varstaMedie = empty($pacienti) ? 0 : 
    round(array_sum(array_column($pacienti, 'varsta')) / count($pacienti), 1);

// Distribuții
$grupeVarsta = ['<60' => 0, '60-69' => 0, '70-79' => 0, '80+' => 0];
foreach ($pacienti as $p) {
    $v = (int)$p['varsta'];
    if ($v < 60) $grupeVarsta['<60']++;
    elseif ($v < 70) $grupeVarsta['60-69']++;
    elseif ($v < 80) $grupeVarsta['70-79']++;
    else $grupeVarsta['80+']++;
}

$tipuriAlarme = [];
foreach ($alarme as $a) {
    $tipuriAlarme[$a['tip_alarma']] = ($tipuriAlarme[$a['tip_alarma']] ?? 0) + 1;
}

// Top pacienți cu alarme
$alarmePerPacient = [];
foreach ($alarme as $a) {
    $alarmePerPacient[$a['id_pacient']] = ($alarmePerPacient[$a['id_pacient']] ?? 0) + 1;
}
arsort($alarmePerPacient);
$topAlarme = array_slice($alarmePerPacient, 0, 5, true);

// Consultații pe lună
$consultatiiPerLuna = [];
for ($i = 5; $i >= 0; $i--) {
    $luna = date('Y-m', strtotime("-{$i} months"));
    $consultatiiPerLuna[$luna] = 0;
}
foreach ($consultatii as $c) {
    $luna = date('Y-m', strtotime($c['data_consultatie']));
    if (isset($consultatiiPerLuna[$luna])) $consultatiiPerLuna[$luna]++;
}

logCurrentUserAction('VIEW', 'Statistici', null, 'Generare raport statistici PDF');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport statistici</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.4; }
        h1 { color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 8px; font-size: 18pt; }
        h2 { color: #1565c0; font-size: 13pt; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .meta { color: #666; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 15px 0; }
        .stat-box { border: 2px solid #1976d2; padding: 12px; text-align: center; border-radius: 4px; }
        .stat-num { font-size: 24pt; font-weight: bold; color: #1976d2; }
        .stat-lbl { font-size: 9pt; color: #666; text-transform: uppercase; }
        .bar { height: 18px; background: #1976d2; display: inline-block; vertical-align: middle; }
        .no-print { margin: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding:10px 20px; background:#1976d2; color:white; border:none; cursor:pointer;">
        🖨 Tipărește / Salvează ca PDF
    </button>
    <a href="<?= url('rapoarte.php') ?>" style="margin-left:10px;">← Înapoi</a>
</div>

<h1>RAPORT STATISTICI</h1>
<div class="meta">
    Generat la <?= e(formatDateTime(date('Y-m-d H:i:s'))) ?> · 
    Medic: <?= e(MedicRepo::fullName($medic)) ?> · 
    Specializare: <?= e($medic['specializare']) ?>
</div>

<h2>1. Indicatori cheie</h2>
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-num"><?= count($pacienti) ?></div>
        <div class="stat-lbl">Pacienți activi</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?= count($consultatii) ?></div>
        <div class="stat-lbl">Consultații total</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?= count($alarme) ?></div>
        <div class="stat-lbl">Alarme total</div>
    </div>
    <div class="stat-box">
        <div class="stat-num"><?= $varstaMedie ?></div>
        <div class="stat-lbl">Vârstă medie</div>
    </div>
</div>

<h2>2. Distribuție pacienți pe grupe de vârstă</h2>
<table>
    <thead><tr><th>Grupă</th><th>Număr pacienți</th><th>Procent</th><th>Grafic</th></tr></thead>
    <tbody>
        <?php $total = max(1, count($pacienti)); ?>
        <?php foreach ($grupeVarsta as $grupa => $nr): 
            $pct = round($nr / $total * 100, 1);
        ?>
            <tr>
                <td><?= e($grupa) ?> ani</td>
                <td><?= $nr ?></td>
                <td><?= $pct ?>%</td>
                <td><div class="bar" style="width: <?= $pct * 2 ?>px;"></div></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>3. Distribuție alarme pe tip</h2>
<?php if (empty($tipuriAlarme)): ?>
    <p><em>Nicio alarmă înregistrată.</em></p>
<?php else: ?>
    <table>
        <thead><tr><th>Tip alarmă</th><th>Număr</th><th>Procent</th><th>Grafic</th></tr></thead>
        <tbody>
            <?php $totalA = max(1, count($alarme)); ?>
            <?php foreach ($tipuriAlarme as $tip => $nr): 
                $pct = round($nr / $totalA * 100, 1);
            ?>
                <tr>
                    <td><strong><?= e(strtoupper($tip)) ?></strong></td>
                    <td><?= $nr ?></td>
                    <td><?= $pct ?>%</td>
                    <td><div class="bar" style="width: <?= $pct * 2 ?>px;"></div></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>4. Top 5 pacienți cu cele mai multe alarme</h2>
<?php if (empty($topAlarme)): ?>
    <p><em>Niciun pacient nu are alarme.</em></p>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Pacient</th><th>Vârstă</th><th>Nr. alarme</th></tr></thead>
        <tbody>
            <?php $i = 1; foreach ($topAlarme as $idP => $nr): 
                $pac = PacientRepo::findById($idP);
                if (!$pac) continue;
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= e(PacientRepo::fullName($pac)) ?></td>
                    <td><?= e($pac['varsta']) ?> ani</td>
                    <td><strong><?= $nr ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>5. Consultații pe ultimele 6 luni</h2>
<table>
    <thead><tr><th>Luna</th><th>Nr. consultații</th><th>Grafic</th></tr></thead>
    <tbody>
        <?php $maxC = max(1, max($consultatiiPerLuna)); ?>
        <?php foreach ($consultatiiPerLuna as $luna => $nr): ?>
            <tr>
                <td><?= e($luna) ?></td>
                <td><?= $nr ?></td>
                <td><div class="bar" style="width: <?= ($nr / $maxC) * 200 ?>px;"></div></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p style="margin-top: 40px; text-align: right;">
    Medic: <strong><?= e(MedicRepo::fullName($medic)) ?></strong><br>
    Semnătură: ________________
</p>

<p style="margin-top:30px; font-size:9pt; color:#666; text-align:center;">
    <?= e(APP_NAME) ?> · Raport intern, uz administrativ
</p>

</body>
</html>