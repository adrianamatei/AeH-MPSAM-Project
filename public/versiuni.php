<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$entitate = $_GET['entitate'] ?? '';
$entitateId = (int)($_GET['id'] ?? 0);

$versiuni = [];
$titlu = 'Istoric versiuni';

if ($entitate && $entitateId) {
    $versiuni = VersionHistoryRepo::findByEntity($entitate, $entitateId);
    $titlu = "Istoric versiuni: {$entitate} #{$entitateId}";
} else {
    $versiuni = VersionHistoryRepo::recent(100);
    $titlu = 'Toate modificările recente';
}

renderHeader($titlu, 'versiuni');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Sistem</div>
        <h1>📜 <?= e($titlu) ?></h1>
    </div>
    <div class="page-actions">
        <span class="badge badge-secondary"><?= count($versiuni) ?> versiuni</span>
    </div>
</div>

<div class="flash flash-info">
    Fiecare modificare a datelor medicale generează automat o versiune nouă (EuroRec GS001539.2, GS001595.1). 
    Versiunile sunt imutabile și permit urmărirea completă a evoluției datelor.
</div>

<?php if (empty($versiuni)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📜</div>
            <h3>Nicio versiune înregistrată</h3>
            <p>Modificările vor apărea aici pe măsură ce datele sunt actualizate.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($versiuni as $i => $v): 
        $dateVechi = json_decode($v['date_vechi'] ?? '{}', true) ?: [];
        $dateNoi = json_decode($v['date_noi'] ?? '{}', true) ?: [];
        
        $clsActiune = match($v['actiune']) {
            'UPDATE' => 'badge-warning',
            'ARCHIVE' => 'badge-danger',
            'STOP' => 'badge-secondary',
            default => 'badge-primary',
        };
    ?>
        <div class="card mb-3">
            <div class="card-header">
                <div>
                    <h3>
                        Versiunea <?= count($versiuni) - $i ?>
                        <span class="badge <?= $clsActiune ?>" style="margin-left:8px;"><?= e($v['actiune']) ?></span>
                    </h3>
                    <div class="text-small text-muted mt-1">
                        <?= e(formatDateTime($v['created_at'])) ?>
                        <?php if (!empty($v['email'])): ?>
                            · de <?= e($v['email']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$entitate): ?>
                    <span class="badge badge-secondary"><?= e($v['entitate']) ?> #<?= e($v['entitate_id']) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($v['actiune'] === 'UPDATE' && !empty($dateVechi) && !empty($dateNoi)):
                    // Arată doar câmpurile modificate
                    $modificari = [];
                    foreach ($dateNoi as $key => $val) {
                        $oldVal = $dateVechi[$key] ?? null;
                        if ($oldVal !== null && (string)$oldVal !== (string)$val) {
                            $modificari[$key] = ['vechi' => $oldVal, 'nou' => $val];
                        }
                    }
                ?>
                    <?php if (!empty($modificari)): ?>
                        <table class="table" style="font-size:0.85rem;">
                            <thead><tr><th>Câmp</th><th>Valoare veche</th><th>→</th><th>Valoare nouă</th></tr></thead>
                            <tbody>
                                <?php foreach ($modificari as $camp => $vals): ?>
                                    <tr>
                                        <td class="text-bold"><?= e($camp) ?></td>
                                        <td style="background:#fff0f0; color:#c00;"><?= e(truncate((string)$vals['vechi'], 100)) ?></td>
                                        <td style="text-align:center;">→</td>
                                        <td style="background:#f0fff0; color:#060;"><?= e(truncate((string)$vals['nou'], 100)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Nicio modificare detectată în câmpurile vizibile.</p>
                    <?php endif; ?>
                    
                <?php elseif ($v['actiune'] === 'ARCHIVE' || $v['actiune'] === 'STOP'): ?>
                    <p class="text-muted">Snapshot salvat la momentul <?= $v['actiune'] === 'ARCHIVE' ? 'arhivării' : 'opririi' ?>:</p>
                    <details>
                        <summary class="text-small" style="cursor:pointer; color: var(--primary);">Arată datele complete</summary>
                        <pre style="background: var(--gray-50); padding: var(--sp-3); border-radius: var(--radius-sm); 
                                    font-size: 0.8rem; overflow-x: auto; margin-top: var(--sp-2);"><?= e(json_encode($dateVechi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php renderFooter(); ?>