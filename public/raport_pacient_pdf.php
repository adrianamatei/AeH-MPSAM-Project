<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);

if (!$pacient || !medicCanAccessPacient($idPacient)) {
    flash('error', 'Pacient invalid.');
    redirect(url('rapoarte.php'));
}

$medic = MedicRepo::findById($pacient['id_medic']);
$consultatii = ConsultatieRepo::findByPacient($idPacient);
$recomandari = RecomandareRepo::findByPacient($idPacient);

logCurrentUserAction('VIEW', 'Pacient', $idPacient, 'Generare raport PDF');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Raport pacient: <?= e(PacientRepo::fullName($pacient)) ?></title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.4; }
        h1 { color: #1976d2; border-bottom: 2px solid #1976d2; padding-bottom: 8px; font-size: 18pt; }
        h2 { color: #1565c0; font-size: 13pt; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        h3 { font-size: 11pt; margin-top: 12px; }
        .meta { color: #666; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
        .alergii { background: #ffebee; padding: 8px; border-left: 4px solid #c62828; margin: 10px 0; }
        .no-print { margin: 20px; }
        @media print { .no-print { display: none; } }
        dl { display: grid; grid-template-columns: 180px 1fr; gap: 4px 12px; margin: 10px 0; }
        dt { font-weight: bold; color: #555; }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding:10px 20px; background:#1976d2; color:white; border:none; cursor:pointer;">
        🖨 Tipărește / Salvează ca PDF
    </button>
    <a href="<?= url('rapoarte.php') ?>" style="margin-left:10px;">← Înapoi</a>
</div>

<h1>RAPORT PACIENT</h1>
<div class="meta">
    Generat la <?= e(formatDateTime(date('Y-m-d H:i:s'))) ?> · 
    Medic: <?= e(MedicRepo::fullName($medic)) ?> · 
    Sistem: <?= e(APP_NAME) ?>
</div>

<h2>1. Date demografice</h2>
<dl>
    <dt>Nume complet:</dt><dd><?= e(PacientRepo::fullName($pacient)) ?></dd>
    <dt>CNP:</dt><dd><?= e($pacient['cnp']) ?></dd>
    <dt>Vârstă:</dt><dd><?= e($pacient['varsta']) ?> ani</dd>
    <dt>Adresă:</dt><dd><?= e($pacient['strada']) ?>, <?= e($pacient['oras']) ?>, <?= e($pacient['judet']) ?></dd>
    <dt>Telefon:</dt><dd><?= e($pacient['telefon']) ?></dd>
    <dt>Email:</dt><dd><?= e($pacient['email']) ?></dd>
    <dt>Profesie:</dt><dd><?= e($pacient['profesie']) ?: '-' ?></dd>
    <dt>Loc de muncă:</dt><dd><?= e($pacient['loc_de_munca']) ?: '-' ?></dd>
</dl>

<h2>2. Istoric medical</h2>
<p><?= nl2br(e($pacient['istoric_medical'])) ?: '<em>Niciun istoric înregistrat</em>' ?></p>

<?php if (!empty($pacient['alergii']) && $pacient['alergii'] !== 'Niciuna'): ?>
<div class="alergii">
    <strong>⚠ ALERGII CUNOSCUTE:</strong><br>
    <?= nl2br(e($pacient['alergii'])) ?>
</div>
<?php endif; ?>

<h2>3. Consultații (<?= count($consultatii) ?>)</h2>
<?php if (empty($consultatii)): ?>
    <p><em>Nicio consultație înregistrată</em></p>
<?php else: ?>
    <table>
        <thead><tr><th>Data</th><th>Diagnostic</th><th>Motiv</th></tr></thead>
        <tbody>
            <?php foreach ($consultatii as $c): ?>
                <tr>
                    <td><?= e(formatDate($c['data_consultatie'])) ?></td>
                    <td><?= e($c['diagnostic']) ?></td>
                    <td><?= e($c['motiv_prezentare']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>4. Recomandări active (<?= count($recomandari) ?>)</h2>
<?php if (empty($recomandari)): ?>
    <p><em>Nicio recomandare</em></p>
<?php else: ?>
    <table>
        <thead><tr><th>Tip</th><th>Indicații</th></tr></thead>
        <tbody>
            <?php foreach ($recomandari as $r): ?>
                <tr>
                    <td><?= e(ucfirst($r['tip_recomandare'])) ?></td>
                    <td><?= e($r['indicatii']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p style="margin-top: 40px; text-align: right;">
    Medic curant: <strong><?= e(MedicRepo::fullName($medic)) ?></strong><br>
    Semnătură: ________________
</p>

</body>
</html>