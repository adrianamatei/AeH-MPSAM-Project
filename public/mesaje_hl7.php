<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$tab = $_GET['tab'] ?? 'toate';

function resolveMedicName($id) {
    $medic = MedicRepo::findById($id);
    if ($medic) {
        return 'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
    }
    $medic = MedicRepo::findByUtilizator($id);
    if ($medic) {
        return 'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
    }
    $pacient = PacientRepo::findById($id);
    if ($pacient) {
        return PacientRepo::fullName($pacient);
    }
    $pacient = PacientRepo::findByUtilizator($id);
    if ($pacient) {
        return PacientRepo::fullName($pacient);
    }
    return 'ID #' . $id;
}

$mesaje = match($tab) {
    'primite' => MesajHL7Repo::primite(),
    'trimise' => MesajHL7Repo::trimise(),
    default => MesajHL7Repo::all(),
};

renderHeader('Mesaje HL7', 'mesaje_hl7');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Comunicare</div>
        <h1>Mesaje HL7 / FHIR</h1>
    </div>
    <div class="page-actions">
        <a href="<?= url('fhir_trimite.php') ?>" class="btn btn-primary">Trimite scrisoare medicala</a>
    </div>
</div>

<div class="tabs">
    <a href="?tab=toate" class="tab <?= $tab === 'toate' ? 'active' : '' ?>">Toate</a>
    <a href="?tab=primite" class="tab <?= $tab === 'primite' ? 'active' : '' ?>">Primite (trimiteri)</a>
    <a href="?tab=trimise" class="tab <?= $tab === 'trimise' ? 'active' : '' ?>">Trimise (scrisori medicale)</a>
</div>

<?php if (empty($mesaje)): ?>
    <div class="card">
        <div class="empty-state">
            <h3>Niciun mesaj</h3>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($mesaje as $m):
        $isPrimit = stripos($m['tip_mesaj'], 'trimitere') !== false;
        
        $sursaNume = $m['sursa'];
        $destNume = $m['destinatie'];
        if (is_numeric($m['sursa'])) {
            $sursaNume = resolveMedicName((int)$m['sursa']);
        }
        if (is_numeric($m['destinatie'])) {
            $destNume = resolveMedicName((int)$m['destinatie']);
        }
    ?>
        <div class="card">
            <div class="card-header">
                <div>
                    <h3><?= $isPrimit ? 'Trimitere primita' : 'Scrisoare medicala trimisa' ?></h3>
                    <div class="text-small text-muted mt-1">
                        <?= e(formatDateTime($m['moment_transmitere'])) ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <dl class="dl-grid mb-3">
                    <dt>De la</dt><dd><?= e($sursaNume) ?></dd>
                    <dt>Către</dt><dd><?= e($destNume) ?></dd>
                </dl>
                <details>
                    <summary style="cursor:pointer; font-size:0.85rem; font-weight:600;">Vezi conținut mesaj</summary>
                    <pre style="background: var(--gray-50); padding: var(--sp-3); border-radius: var(--radius-sm); 
                                overflow-x: auto; font-size: 0.8rem; white-space: pre-wrap; max-height: 300px; margin-top: 8px;"><?= e($m['continut']) ?></pre>
                </details>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php renderFooter(); ?>