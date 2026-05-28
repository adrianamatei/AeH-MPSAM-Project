<?php
require_once __DIR__ . '/../app/config.php';
requireRole('pacient');

$idPacient = currentPacientId();
$pacient = PacientRepo::findById($idPacient);

if (!$pacient) {
    flash('error', 'Profil pacient negăsit.');
    redirect(url('logout.php'));
}

$medic = MedicRepo::findById($pacient['id_medic']);
$ultimeleValori = MasuratoriRepo::ultimeleValori($idPacient);
$alarmeRecente = array_slice(AlarmaRepo::findByPacient($idPacient), 0, 3);
$activitatiAzi = ActivitateRepo::activitatiAzi($idPacient);
$recomandari = RecomandareRepo::findByPacient($idPacient);
$praguri = PraguriRepo::findByPacient($idPacient);

/**
 * Helper - returnează clasa CSS pentru o valoare în funcție de praguri
 */
function getVitalClass($valoare, $tipParametru, $praguri) {
    if ($valoare === null) return '';
    switch ($tipParametru) {
        case 'puls':
            if ($valoare > $praguri['max_puls'] || $valoare < $praguri['min_puls']) return 'danger';
            if ($valoare > $praguri['max_puls'] * 0.95 || $valoare < $praguri['min_puls'] * 1.05) return 'warning';
            return 'normal';
        case 'temperatura':
            if ($valoare > $praguri['max_temp']) return 'danger';
            if ($valoare > $praguri['max_temp'] - 0.5) return 'warning';
            return 'normal';
    }
    return 'normal';
}

renderHeader('Dashboard', 'dashboard');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Principal</div>
        <h1>Bună ziua, <?= e($pacient['prenume']) ?>!</h1>
    </div>
</div>

<!-- INFO MEDIC -->
<div class="card">
    <div class="card-body">
        <div class="d-flex align-center gap-3">
            <div class="patient-avatar" style="background:var(--success-bg); color:var(--success);">⚕</div>
            <div>
                <div class="text-muted text-small">Medicul tău curant</div>
                <div class="text-bold"><?= e(MedicRepo::fullName($medic)) ?></div>
                <div class="text-small text-muted">
                    <?= e($medic['specializare'] ?? '') ?> · 
                    <?= e($medic['telefon'] ?? '') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VITALS LIVE -->
<div class="card">
    <div class="card-header">
        <h3>📊 Ultimele valori măsurate</h3>
        <a href="<?= url('monitorizare.php') ?>" class="btn btn-sm btn-outline">Vezi grafice</a>
    </div>
    <div class="card-body">
        <?php if (empty($ultimeleValori)): ?>
            <div class="empty-state">
                <p>Nu s-au înregistrat încă măsurători de la senzori.</p>
            </div>
        <?php else: ?>
            <div class="vitals-grid">
                <?php 
                $iconuri = ['puls' => '❤', 'temperatura' => '🌡'];
                $unitati = ['puls' => 'bpm', 'temperatura' => '°C'];
                $labeluri = ['puls' => 'Puls', 'temperatura' => 'Temperatură'];
                foreach (['puls', 'temperatura'] as $tip):
                    $m = $ultimeleValori[$tip] ?? null;
                    if (!$m) continue;
                    $cls = getVitalClass($m['valoare'], $tip, $praguri);
                ?>
                    <div class="vital-widget">
                        <div class="vital-icon"><?= $iconuri[$tip] ?></div>
                        <div class="vital-label"><?= $labeluri[$tip] ?></div>
                        <div class="vital-number">
                            <span class="vital-value <?= $cls ?>"><?= e($m['valoare']) ?></span>
                            <span class="vital-unit"><?= e($unitati[$tip]) ?></span>
                        </div>
                        <div class="vital-time"><?= e(formatDateTime($m['moment_inregistrare'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-small text-muted mt-3">
                Praguri personalizate: 
                Puls <?= e($praguri['min_puls']) ?>-<?= e($praguri['max_puls']) ?> bpm,
                Temp max <?= e($praguri['max_temp']) ?>°C
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- DOUĂ COLOANE -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4);">

    <!-- ACTIVITĂȚI AZI -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>✅ Activitățile mele de azi</h3>
            <a href="<?= url('activitati.php') ?>" class="btn btn-sm btn-outline">Vezi toate</a>
        </div>
        <div class="card-body">
            <?php if (empty($activitatiAzi)): ?>
                <div class="empty-state">
                    <p class="text-muted">Nu ai activități programate astăzi.</p>
                </div>
            <?php else: ?>
                <?php foreach ($activitatiAzi as $act): ?>
                    <div class="d-flex align-center gap-3" style="padding: var(--sp-3) 0; border-bottom: 1px solid var(--gray-100);">
                        <div style="font-size:1.5rem;">
                            <?= $act['este_finalizata'] ? '✅' : '⏰' ?>
                        </div>
                        <div style="flex-grow:1;">
                            <div class="text-bold"><?= e($act['nume_activitate']) ?></div>
                            <div class="text-small text-muted">
                                Ora <?= e($act['ora_programata']) ?> · 
                                <?= e(truncate($act['descriere'], 60)) ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($act['este_finalizata']): ?>
                                <span class="badge badge-success">Finalizat</span>
                            <?php else: ?>
                                <span class="badge badge-warning">De făcut</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ALARME RECENTE -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <h3>⚠ Alarme recente</h3>
            <a href="<?= url('alarme.php') ?>" class="btn btn-sm btn-outline">Vezi toate</a>
        </div>
        <div class="card-body">
            <?php if (empty($alarmeRecente)): ?>
                <div class="empty-state">
                    <div style="font-size:2rem;">✓</div>
                    <p class="text-muted">Nicio alarmă recentă. Valorile sunt în limite normale.</p>
                </div>
            <?php else: ?>
                <?php foreach ($alarmeRecente as $a): ?>
                    <div class="d-flex align-center gap-3" style="padding: var(--sp-3) 0; border-bottom: 1px solid var(--gray-100);">
                        <div style="font-size:1.5rem;">⚠</div>
                        <div style="flex-grow:1;">
                            <div class="text-bold"><?= e(strtoupper($a['tip_alarma'])) ?>: <?= e($a['valoare_declansare']) ?></div>
                            <div class="text-small text-muted">
                                <?= e(formatDateTime($a['moment_declansare'])) ?> · 
                                <?= e(truncate($a['mesaj'], 50)) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- RECOMANDĂRI ACTIVE -->
<?php if (!empty($recomandari)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3>💊 Recomandări medic</h3>
        <a href="<?= url('recomandari.php') ?>" class="btn btn-sm btn-outline">Vezi toate</a>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Tip</th>
                    <th>Indicații</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recomandari as $r): ?>
                    <tr>
                        <td><span class="badge badge-primary"><?= e(strtoupper($r['tip_recomandare'])) ?></span></td>
                        <td><?= e($r['indicatii']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
