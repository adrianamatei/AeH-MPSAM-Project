<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$tab = $_GET['tab'] ?? 'toate';
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
</div>

<div class="tabs">
    <a href="?tab=toate" class="tab <?= $tab === 'toate' ? 'active' : '' ?>">Toate</a>
    <a href="?tab=primite" class="tab <?= $tab === 'primite' ? 'active' : '' ?>">📥 Primite (trimiteri)</a>
    <a href="?tab=trimise" class="tab <?= $tab === 'trimise' ? 'active' : '' ?>">📤 Trimise (scrisori medicale FHIR)</a>
</div>

<?php if (empty($mesaje)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">✉</div>
            <h3>Niciun mesaj</h3>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($mesaje as $m):
        $pacient = $m['id_pacient'] ? PacientRepo::findById($m['id_pacient']) : null;
        $isPrimit = stripos($m['tip_mesaj'], 'trimitere') !== false;
    ?>
        <div class="card">
            <div class="card-header">
                <div>
                    <h3><?= $isPrimit ? '📥' : '📤' ?> <?= e($m['tip_mesaj']) ?></h3>
                    <div class="text-small text-muted mt-1">
                        <?= e(formatDateTime($m['moment_transmitere'])) ?>
                        <?php if ($pacient): ?>
                            · Pacient: <a href="<?= url('pacient_detalii.php?id=' . $pacient['id']) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <dl class="dl-grid mb-3">
                    <dt>De la</dt><dd><?= e($m['sursa']) ?></dd>
                    <dt>Către</dt><dd><?= e($m['destinatie']) ?></dd>
                </dl>
                <div class="form-label">Conținut mesaj:</div>
                <pre style="background: var(--gray-50); padding: var(--sp-3); border-radius: var(--radius-sm); 
                            overflow-x: auto; font-size: 0.85rem; white-space: pre-wrap;"><?= e($m['continut']) ?></pre>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php renderFooter(); ?>