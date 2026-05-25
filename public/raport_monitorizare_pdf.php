<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);

if (!$pacient || !medicCanAccessPacient($idPacient)) {
    flash('error', 'Pacient invalid.');
    redirect(url('rapoarte.php'));
}

$startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endDate = date('Y-m-d H:i:s');
$pulsData = MasuratoriRepo::findByPacientInterval($idPacient, $startDate, $endDate, 'puls');
$tempData = MasuratoriRepo::findByPacientInterval($idPacient, $startDate, $endDate, 'temperatura');

function statistics($data) {
    if (empty($data)) return ['min'=>'-', 'max'=>'-', 'avg'=>'-', 'count'=>0];
    $vals = array_map(fn($d) => (float)$d['valoare'], $data);
    return [
        'min' => min($vals),
        'max' => max($vals),
        'avg' => round(array_sum($vals) / count($vals), 1),
        'count' => count($vals),
    ];
}
$pulsStats = statistics($pulsData);
$tempStats = statistics($tempData);
$praguri = PraguriRepo::findByPacient($idPacient);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport monitorizare: <?= e(PacientRepo::fullName($pacient)) ?></title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
        h1 { color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 8px; }
        h2 { color: #1565c0; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .no-print { margin: 20px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" style="padding:10px 20px; background:#1976d2; color:white; border:none;">
        🖨 Tipărește / Salvează PDF
    </button>
</div>

<h1>RAPORT MONITORIZARE - <?= e(PacientRepo::fullName($pacient)) ?></h1>
<p>
    CNP: <?= e($pacient['cnp']) ?> · 
    Vârstă: <?= e($pacient['varsta']) ?> ani · 
    Interval: ultimele 24 ore
</p>

<h2>Sumar statistici</h2>
<table>
    <thead>
        <tr><th>Parametru</th><th>Minim</th><th>Maxim</th><th>Medie</th><th>Nr. măsurători</th><th>Prag min</th><th>Prag max</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Puls (bpm)</strong></td>
            <td><?= e($pulsStats['min']) ?></td>
            <td><?= e($pulsStats['max']) ?></td>
            <td><?= e($pulsStats['avg']) ?></td>
            <td><?= e($pulsStats['count']) ?></td>
            <td><?= e($praguri['min_puls']) ?></td>
            <td><?= e($praguri['max_puls']) ?></td>
        </tr>
        <tr>
            <td><strong>Temperatură (°C)</strong></td>
            <td><?= e($tempStats['min']) ?></td>
            <td><?= e($tempStats['max']) ?></td>
            <td><?= e($tempStats['avg']) ?></td>
            <td><?= e($tempStats['count']) ?></td>
            <td>35.0</td>
            <td><?= e($praguri['max_temp']) ?></td>
        </tr>
    </tbody>
</table>

<p style="margin-top:30px; font-size:10pt; color:#666;">
    Generat la <?= e(formatDateTime(date('Y-m-d H:i:s'))) ?> · <?= e(APP_NAME) ?>
</p>
</body>
</html>