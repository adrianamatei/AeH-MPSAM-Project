<?php
require_once __DIR__ . '/../app/config.php';
requireRole('pacient');

$idPacient = currentPacientId();
$pacient = PacientRepo::findById($idPacient);

if (!$pacient) {
    flash('error', 'Profil negăsit.');
    redirect(url('logout.php'));
}

$medic = MedicRepo::findById($pacient['id_medic']);

renderHeader('Fișa mea', 'profil_pacient');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Principal</div>
        <h1>Fișa mea medicală</h1>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>👤 Date personale</h3>
    </div>
    <div class="card-body">
        <dl class="dl-grid">
            <dt>Nume complet</dt><dd><?= e(PacientRepo::fullName($pacient)) ?></dd>
            <dt>CNP</dt><dd><?= e($pacient['cnp']) ?></dd>
            <dt>Vârstă</dt><dd><?= e($pacient['varsta']) ?> ani</dd>
            <dt>Telefon</dt><dd><?= e($pacient['telefon']) ?></dd>
            <dt>Email</dt><dd><?= e($pacient['email']) ?></dd>
            <dt>Adresă</dt>
            <dd>
                <?= e($pacient['strada']) ?>, 
                <?= e($pacient['oras']) ?>, 
                <?= e($pacient['judet']) ?>
            </dd>
            <dt>Profesie</dt><dd><?= e($pacient['profesie']) ?: '-' ?></dd>
        </dl>
    </div>
    <div class="card-footer text-small text-muted">
        Pentru a modifica datele, contactați medicul dumneavoastră curant.
    </div>
</div>

<?php if ($medic): ?>
<div class="card">
    <div class="card-header"><h3>⚕ Medic curant</h3></div>
    <div class="card-body">
        <dl class="dl-grid">
            <dt>Nume</dt><dd><?= e(MedicRepo::fullName($medic)) ?></dd>
            <dt>Specializare</dt><dd><?= e($medic['specializare']) ?></dd>
            <dt>Telefon</dt><dd><?= e($medic['telefon']) ?></dd>
            <dt>Email</dt><dd><?= e($medic['email']) ?></dd>
        </dl>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>🏥 Date medicale</h3></div>
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

<div class="d-flex gap-2 mt-4">
    <a href="<?= url('dashboard_pacient.php') ?>" class="btn">← Înapoi la dashboard</a>
    <a href="<?= url('monitorizare.php') ?>" class="btn btn-outline">📊 Vezi monitorizare</a>
    <a href="<?= url('profil.php') ?>" class="btn">🔐 Schimbă parola</a>
</div>

<?php renderFooter(); ?>