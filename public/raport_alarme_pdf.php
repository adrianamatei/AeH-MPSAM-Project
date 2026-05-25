<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$idPacientFilter = (int)($_GET['id_pacient'] ?? 0);

if ($idPacientFilter) {
    if (!medicCanAccessPacient($idPacientFilter)) {
        flash('error', 'Acces interzis.');
        redirect(url('rapoarte.php'));
    }
    $alarme = AlarmaRepo::findByPacient($idPacientFilter);
    $pacientFiltrat = PacientRepo::findById($idPacientFilter);
} else {
    $alarme = AlarmaRepo::findByMedic($idMedic);
    $pacientFiltrat = null;
}

$medic = MedicRepo::findById($idMedic);

// Statistici
$tipuri = [];
foreach ($alarme as $a) {
    $tipuri[$a['tip_alarma']] = ($tipuri[$a['tip_alarma']] ?? 0) + 1;
}

logCurrentUserAction('VIEW', 'Alarme', null, 'Generare raport PDF alarme');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport alarme</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.4; }
        h1 { color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 8px; font-size: 18pt; }
        h2 { color: #1565c0; font-size: 13pt; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .meta { color: #666; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10pt; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
        .tip-puls { background: #ffebee; }
        .tip-temperatura, .tip-temp { background: #fff8e1; }
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

<h1>RAPORT ALARME</h1>
<div class="meta">
    Generat la <?= e(formatDateTime(date('Y-m-d H:i:s'))) ?> · 
    Medic: <?= e(MedicRepo::fullName($medic)) ?> · 
    <?= $pacientFiltrat ? 'Pacient: ' . e(PacientRepo::fullName($pacientFiltrat)) : 'Toți pacienții' ?>
</div>

<h2>Sumar</h2>
<table style="width:auto;">
    <tr><th>Total alarme</th><td><?= count($alarme) ?></td></tr>
    <?php foreach ($tipuri as $tip => $nr): ?>
        <tr><th>Alarme tip <?= e(strtoupper($tip)) ?></th><td><?= $nr ?></td></tr>
    <?php endforeach; ?>
</table>

<h2>Listă completă alarme</h2>
<?php if (empty($alarme)): ?>
    <p><em>Nicio alarmă înregistrată.</em></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Moment</th>
                <?php if (!$pacientFiltrat): ?><th>Pacient</th><?php endif; ?>
                <th>Tip</th>
                <th>Valoare</th>
                <th>Praguri</th>
                <th>Durată (s)</th>
                <th>Mesaj</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alarme as $a):
                $pac = PacientRepo::findById($a['id_pacient']);
            ?>
                <tr class="tip-<?= e($a['tip_alarma']) ?>">
                    <td><?= e(formatDateTime($a['moment_declansare'])) ?></td>
                    <?php if (!$pacientFiltrat): ?>
                        <td><?= e(PacientRepo::fullName($pac)) ?></td>
                    <?php endif; ?>
                    <td><strong><?= e(strtoupper($a['tip_alarma'])) ?></strong></td>
                    <td><strong><?= e($a['valoare_declansare']) ?></strong></td>
                    <td><?= e($a['prag_minim']) ?> - <?= e($a['prag_maxim']) ?></td>
                    <td><?= e($a['durata_persistenta']) ?></td>
                    <td><?= e($a['mesaj']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p style="margin-top: 40px; text-align: right;">
    Medic: <strong><?= e(MedicRepo::fullName($medic)) ?></strong><br>
    Semnătură: ________________
</p>

<p style="margin-top:30px; font-size:9pt; color:#666; text-align:center;">
    <?= e(APP_NAME) ?> · Document confidențial - Secret medical
</p>

</body>
</html>