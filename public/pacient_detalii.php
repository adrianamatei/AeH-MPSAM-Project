<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);

if (!$pacient) {
    flash('error', 'Pacient negăsit.');
    redirect(url('pacienti.php'));
}

// Verifică acces (medic propriu sau pacient însuși)
requireAccessToPacient($idPacient);

$medic = MedicRepo::findById($pacient['id_medic']);
$consultatii = ConsultatieRepo::findByPacient($idPacient);
$alarme = AlarmaRepo::findByPacient($idPacient);
$recomandari = RecomandareRepo::findByPacient($idPacient);
$praguri = PraguriRepo::findByPacient($idPacient);
$dispozitive = DispozitivRepo::findByPacient($idPacient);
$ultimeleValori = MasuratoriRepo::ultimeleValori($idPacient);

$isMedic = hasRole('medic');

renderHeader('Fișa: ' . PacientRepo::fullName($pacient), 'pacienti');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <?php if ($isMedic): ?>
                <a href="<?= url('pacienti.php') ?>">Pacienți</a> / 
            <?php endif; ?>
            <?= e(PacientRepo::fullName($pacient)) ?>
        </div>
        <h1>
            <?= e(PacientRepo::fullName($pacient)) ?>
            <span class="text-muted" style="font-size:1rem; font-weight:400;"> · <?= e($pacient['varsta']) ?> ani</span>
        </h1>
    </div>
    <?php if ($isMedic): ?>
    <div class="page-actions">
        <a href="<?= url('consultatie_adauga.php?id_pacient=' . $idPacient) ?>" class="btn btn-primary">+ Consultație</a>
        <a href="<?= url('pacient_editare.php?id=' . $idPacient) ?>" class="btn btn-outline">Editează</a>
        <a href="<?= url('raport_pacient_pdf.php?id=' . $idPacient) ?>" class="btn">📄 Raport PDF</a>
    </div>
    <?php endif; ?>
</div>

<!-- Sumar pacient (afișat pe toate ecranele - criteriul EuroRec GS002497.3) -->
<div class="card">
    <div class="card-body">
        <div class="d-flex align-center gap-4 flex-wrap">
            <div class="patient-avatar" style="width:64px; height:64px; font-size:1.4rem;">
                <?= e(mb_substr($pacient['nume'], 0, 1) . mb_substr($pacient['prenume'], 0, 1)) ?>
            </div>
            <div style="flex-grow:1;">
                <div class="text-bold" style="font-size:1.15rem;"><?= e(PacientRepo::fullName($pacient)) ?></div>
                <div class="text-muted">
                    CNP: <?= e($pacient['cnp']) ?> · 
                    <?= e($pacient['varsta']) ?> ani · 
                    <?= e($pacient['telefon']) ?>
                </div>
            </div>
            <?php if ($isMedic && $medic): ?>
                <div class="text-right text-small text-muted">
                    Medic curant:<br>
                    <span class="text-bold"><?= e(MedicRepo::fullName($medic)) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Ultimele valori vitale -->
<?php if (!empty($ultimeleValori)): ?>
<div class="card">
    <div class="card-header">
        <h3>📊 Ultimele valori măsurate</h3>
        <a href="<?= url('monitorizare.php?id=' . $idPacient) ?>" class="btn btn-sm btn-outline">Vezi grafice</a>
    </div>
    <div class="card-body">
        <div class="vitals-grid">
            <?php 
            $iconuri = ['puls' => '❤', 'temperatura' => '🌡'];
            $unitati = ['puls' => 'bpm', 'temperatura' => '°C'];
            $labeluri = ['puls' => 'Puls', 'temperatura' => 'Temperatură'];
            foreach (['puls', 'temperatura'] as $tip):
                if (!isset($ultimeleValori[$tip])) continue;
                $m = $ultimeleValori[$tip];
            ?>
                <div class="vital-widget">
                    <div class="vital-icon"><?= $iconuri[$tip] ?></div>
                    <div class="vital-label"><?= $labeluri[$tip] ?></div>
                    <div class="vital-number">
                        <span class="vital-value normal"><?= e($m['valoare']) ?></span>
                        <span class="vital-unit"><?= e($unitati[$tip]) ?></span>
                    </div>
                    <div class="vital-time"><?= e(formatDateTime($m['moment_inregistrare'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Două coloane: date administrative + medicale -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4);">
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-header"><h3>Date demografice</h3></div>
        <div class="card-body">
            <dl class="dl-grid">
                <dt>Nume</dt><dd><?= e($pacient['nume']) ?></dd>
                <dt>Prenume</dt><dd><?= e($pacient['prenume']) ?></dd>
                <dt>CNP</dt><dd><?= e($pacient['cnp']) ?></dd>
                <dt>Vârstă</dt><dd><?= e($pacient['varsta']) ?> ani</dd>
                <dt>Adresă</dt><dd><?= e($pacient['strada']) ?>, <?= e($pacient['oras']) ?>, <?= e($pacient['judet']) ?></dd>
                <dt>Telefon</dt><dd><?= e($pacient['telefon']) ?></dd>
                <dt>Email</dt><dd><?= e($pacient['email']) ?></dd>
                <dt>Profesie</dt><dd><?= e($pacient['profesie']) ?: '-' ?></dd>
                <dt>Loc de muncă</dt><dd><?= e($pacient['loc_de_munca']) ?: '-' ?></dd>
            </dl>
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-header"><h3>Date medicale</h3></div>
        <div class="card-body">
            <div class="form-group">
                <div class="form-label">Istoric medical</div>
                <div style="white-space:pre-wrap;"><?= e($pacient['istoric_medical']) ?: '-' ?></div>
            </div>
            <div class="form-group">
                <div class="form-label">⚠ Alergii cunoscute</div>
                <div class="text-danger text-bold" style="white-space:pre-wrap;">
                    <?= e($pacient['alergii']) ?: 'Niciuna' ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Praguri personalizate -->
<div class="card mt-4">
    <div class="card-header">
        <h3>🎯 Praguri de monitorizare</h3>
        <?php if ($isMedic): ?>
            <a href="<?= url('praguri_pacient.php?id=' . $idPacient) ?>" class="btn btn-sm btn-outline">Modifică</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="vitals-grid">
            <div class="vital-widget">
                <div class="vital-label">Puls min</div>
                <div class="vital-number"><?= e($praguri['min_puls']) ?> <span class="vital-unit">bpm</span></div>
            </div>
            <div class="vital-widget">
                <div class="vital-label">Puls max</div>
                <div class="vital-number"><?= e($praguri['max_puls']) ?> <span class="vital-unit">bpm</span></div>
            </div>
            <div class="vital-widget">
                <div class="vital-label">Temp max</div>
                <div class="vital-number"><?= e($praguri['max_temp']) ?> <span class="vital-unit">°C</span></div>
            </div>
        </div>
    </div>
</div>

<!-- Consultații -->
<div class="card mt-4">
    <div class="card-header">
        <h3>📋 Consultații (<?= count($consultatii) ?>)</h3>
        <a href="<?= url('consultatii.php?id_pacient=' . $idPacient) ?>" class="btn btn-sm btn-outline">Vezi toate</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($consultatii)): ?>
            <div class="empty-state"><p class="text-muted">Nicio consultație înregistrată.</p></div>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Data</th><th>Diagnostic</th><th>Motiv</th><th></th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($consultatii, 0, 5) as $c): ?>
                        <tr>
                            <td><?= e(formatDate($c['data_consultatie'])) ?></td>
                            <td><?= e(truncate($c['diagnostic'], 60)) ?></td>
                            <td><?= e(truncate($c['motiv_prezentare'], 60)) ?></td>
                            <td class="actions">
                                <a href="<?= url('consultatie_detalii.php?id=' . $c['id']) ?>" class="btn btn-sm btn-outline">Vezi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Alarme + Recomandări -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); margin-top: var(--sp-4);">
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>⚠ Alarme (<?= count($alarme) ?>)</h3>
            <a href="<?= url('alarme.php?id_pacient=' . $idPacient) ?>" class="btn btn-sm btn-outline">Vezi toate</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($alarme)): ?>
                <div class="empty-state"><p class="text-muted">Nicio alarmă.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Tip</th><th>Valoare</th><th>Moment</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($alarme, 0, 5) as $a): ?>
                            <tr>
                                <td><span class="badge badge-danger"><?= e(strtoupper($a['tip_alarma'])) ?></span></td>
                                <td class="vital-value danger"><?= e($a['valoare_declansare']) ?></td>
                                <td class="text-small text-muted"><?= e(formatDateTime($a['moment_declansare'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>💊 Recomandări (<?= count($recomandari) ?>)</h3>
            <?php if ($isMedic): ?>
                <a href="<?= url('recomandare_adauga.php?id_pacient=' . $idPacient) ?>" class="btn btn-sm btn-outline">+ Adaugă</a>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($recomandari)): ?>
                <div class="empty-state"><p class="text-muted">Nicio recomandare.</p></div>
            <?php else: ?>
                <table class="table">
                    <thead><tr><th>Tip</th><th>Indicații</th></tr></thead>
                    <tbody>
                        <?php foreach ($recomandari as $r): ?>
                            <tr>
                                <td><span class="badge badge-primary"><?= e($r['tip_recomandare']) ?></span></td>
                                <td><?= e(truncate($r['indicatii'], 80)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<!-- Dispozitive -->
<?php if (!empty($dispozitive)): ?>
<div class="card mt-4">
    <div class="card-header"><h3>📱 Dispozitive asociate</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead><tr><th>Tip</th><th>Detalii</th><th>Stare</th></tr></thead>
            <tbody>
                <?php foreach ($dispozitive as $d): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= e($d['tip_dispozitiv']) ?></span></td>
                        <td><?= e($d['detalii']) ?></td>
                        <td>
                            <?php if ($d['stare'] === 'activ'): ?>
                                <span class="badge badge-success">Activ</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactiv</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php renderFooter(); ?>