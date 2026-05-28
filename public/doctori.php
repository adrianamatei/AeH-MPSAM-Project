<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$medici = MedicRepo::all();

renderHeader('Medici', 'doctori');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Sistem</div>
        <h1>Echipa medicală</h1>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($medici)): ?>
            <div class="empty-state">
                <div class="empty-icon">👨‍⚕</div>
                <h3>Niciun medic înregistrat</h3>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Medic</th>
                        <th>Specializare</th>
                        <th>Telefon</th>
                        <th>Pacienți</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medici as $m): 
                        $nrPacienti = PacientRepo::count($m['id']);
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <div class="patient-avatar" style="width:36px; height:36px; font-size:0.9rem; background:var(--success-bg); color:var(--success);">
                                        <?= e(mb_substr($m['nume'] ?? '', 0, 1) . mb_substr($m['prenume'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div class="text-bold"><?= e(MedicRepo::fullName($m)) ?></div>
                                </div>
                            </td>
                            <td><span class="badge badge-primary"><?= e($m['specializare']) ?></span></td>
                            <td><?= e($m['telefon']) ?></td>
                            <td><span class="badge badge-secondary"><?= $nrPacienti ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <div class="card-footer text-small text-muted">
        Total: <?= count($medici) ?> medici
    </div>
</div>

<?php renderFooter(); ?>