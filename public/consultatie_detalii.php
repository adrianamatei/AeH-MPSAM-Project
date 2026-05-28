<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$idConsultatie = (int)($_GET['id'] ?? 0);
$consultatie = ConsultatieRepo::findById($idConsultatie);

if (!$consultatie) {
    flash('error', 'Consultație negăsită.');
    redirect(url('consultatii.php'));
}

requireAccessToPacient($consultatie['id_pacient']);

$pacient = PacientRepo::findById($consultatie['id_pacient']);
$medic = MedicRepo::findById($consultatie['id_medic']);

renderHeader('Consultație', 'consultatii');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('consultatii.php') ?>">Consultații</a> / 
            <?= e(formatDate($consultatie['data_consultatie'])) ?>
        </div>
        <h1>Consultație medicală</h1>
    </div>
</div>

<!-- Sumar -->
<div class="card">
    <div class="card-body">
        <dl class="dl-grid">
            <dt>Data & ora</dt>
            <dd class="text-bold"><?= e(formatDateTime($consultatie['data_consultatie'])) ?></dd>
            
            <dt>Pacient</dt>
            <dd>
                <a href="<?= url('pacient_detalii.php?id=' . $pacient['id']) ?>" class="text-bold">
                    <?= e(PacientRepo::fullName($pacient)) ?>
                </a>
                · CNP <?= e($pacient['cnp']) ?> · <?= e($pacient['varsta']) ?> ani
            </dd>
            
            <dt>Medic</dt>
            <dd><?= e(MedicRepo::fullName($medic)) ?> · <?= e($medic['specializare']) ?></dd>
        </dl>
    </div>
</div>

<!-- Motivul prezentării -->
<div class="card">
    <div class="card-header"><h3>📝 Motiv prezentare</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['motiv_prezentare']) ?></p>
    </div>
</div>

<!-- Simptome -->
<?php if (!empty($consultatie['simptome'])): ?>
<div class="card">
    <div class="card-header"><h3>🔍 Simptome</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['simptome']) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Diagnostic -->
<div class="card">
    <div class="card-header"><h3>🩺 Diagnostic</h3></div>
    <div class="card-body">
        <p class="text-bold" style="white-space:pre-wrap;"><?= e($consultatie['diagnostic']) ?></p>
    </div>
</div>

<!-- Rețete -->
<?php if (!empty($consultatie['retete'])): ?>
<div class="card">
    <div class="card-header"><h3>💊 Rețete / Tratament</h3></div>
    <div class="card-body">
        <pre style="white-space:pre-wrap; font-family:inherit;"><?= e($consultatie['retete']) ?></pre>
    </div>
</div>
<?php endif; ?>

<!-- Trimiteri -->
<?php if (!empty($consultatie['trimiteri'])): ?>
<div class="card">
    <div class="card-header"><h3>📨 Trimiteri</h3></div>
    <div class="card-body">
        <p style="white-space:pre-wrap;"><?= e($consultatie['trimiteri']) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- ⚠ Alergii pacient (afișate întotdeauna pentru siguranță - GS002582.2) -->
<?php if (!empty($pacient['alergii']) && $pacient['alergii'] !== 'Niciuna' && $pacient['alergii'] !== 'Niciuna cunoscută'): ?>
<div class="card" style="border-color: var(--danger);">
    <div class="card-header" style="background: var(--danger-bg);">
        <h3 class="text-danger">⚠ Atenție: Alergii cunoscute</h3>
    </div>
    <div class="card-body">
        <p class="text-danger text-bold"><?= e($pacient['alergii']) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="d-flex gap-2 mt-4">
    <a href="<?= url('consultatii.php') ?>" class="btn">← Înapoi la listă</a>
    <a href="<?= url('pacient_detalii.php?id=' . $pacient['id']) ?>" class="btn">Vezi fișa pacient</a>
    <?php if (hasRole('medic')): ?>
        <a href="<?= url('consultatie_adauga.php?id_pacient=' . $pacient['id']) ?>" class="btn btn-primary">+ Consultație nouă</a>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
