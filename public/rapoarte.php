<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$pacienti = PacientRepo::findByMedic(currentMedicId());

renderHeader('Rapoarte', 'rapoarte');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Rapoarte</div>
        <h1>Generare rapoarte</h1>
    </div>
</div>

<div class="dashboard-grid">
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-body">
            <h3>📄 Raport pacient</h3>
            <p class="text-muted">Fișa completă a unui pacient: date demografice, istoric, alergii.</p>
            <form method="GET" action="<?= url('raport_pacient_pdf.php') ?>" target="_blank">
                <div class="form-group">
                    <select name="id" class="form-control" required>
                        <option value="">— Selectează pacient —</option>
                        <?php foreach ($pacienti as $p): ?>
                            <option value="<?= e($p['id']) ?>"><?= e(PacientRepo::fullName($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Generează PDF</button>
            </form>
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-body">
            <h3>📊 Raport monitorizare</h3>
            <p class="text-muted">Valorile parametrilor fiziologici pe interval de timp.</p>
            <form method="GET" action="<?= url('raport_monitorizare_pdf.php') ?>" target="_blank">
                <div class="form-group">
                    <select name="id" class="form-control" required>
                        <option value="">— Selectează pacient —</option>
                        <?php foreach ($pacienti as $p): ?>
                            <option value="<?= e($p['id']) ?>"><?= e(PacientRepo::fullName($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Generează PDF</button>
            </form>
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-body">
            <h3>⚠ Raport alarme</h3>
            <p class="text-muted">Lista alarmelor și avertizărilor declanșate.</p>
            <form method="GET" action="<?= url('raport_alarme_pdf.php') ?>" target="_blank">
                <div class="form-group">
                    <select name="id_pacient" class="form-control">
                        <option value="">— Toți pacienții —</option>
                        <?php foreach ($pacienti as $p): ?>
                            <option value="<?= e($p['id']) ?>"><?= e(PacientRepo::fullName($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Generează PDF</button>
            </form>
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-body">
            <h3>📈 Raport statistici</h3>
            <p class="text-muted">Statistici generale despre activitatea medicului.</p>
            <a href="<?= url('raport_statistici_pdf.php') ?>" target="_blank" 
               class="btn btn-primary btn-block">Generează PDF</a>
        </div>
    </div>
    
</div>

<?php renderFooter(); ?>