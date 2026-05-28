<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$entries = AuditRepo::all(200);

renderHeader('Jurnal acțiuni', 'audit_log');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Sistem</div>
        <h1>Jurnal acțiuni (audit log)</h1>
    </div>
</div>

<div class="flash flash-info">
    Acest jurnal înregistrează toate acțiunile importante din sistem conform criteriilor 
    EuroRec (GS002182, GS002184, GS002198). Înregistrările sunt imutabile.
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Niciun eveniment înregistrat încă</h3>
                <p>Acțiunile vor apărea aici pe măsură ce sunt efectuate.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Moment</th>
                        <th>Utilizator</th>
                        <th>Acțiune</th>
                        <th>Entitate</th>
                        <th>Detalii</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $e):
                        $u = $e['id_utilizator'] ? UtilizatorRepo::findById($e['id_utilizator']) : null;
                    ?>
                        <tr>
                            <td class="text-small"><?= e(formatDateTime($e['timestamp'])) ?></td>
                            <td class="text-small"><?= $u ? e($u['email']) : '<em>anonim</em>' ?></td>
                            <td>
                                <?php 
                                $cls = match($e['action']) {
                                    'LOGIN', 'CREATE' => 'badge-success',
                                    'LOGOUT', 'VIEW' => 'badge-secondary',
                                    'UPDATE', 'CHANGE_PASSWORD' => 'badge-warning',
                                    'DELETE', 'LOGIN_FAILED' => 'badge-danger',
                                    default => 'badge-primary',
                                };
                                ?>
                                <span class="badge <?= $cls ?>"><?= e($e['action']) ?></span>
                            </td>
                            <td class="text-small">
                                <?= e($e['entity']) ?>
                                <?php if ($e['entity_id']): ?>
                                    #<?= e($e['entity_id']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-small"><?= e(truncate($e['details'], 60)) ?></td>
                            <td class="text-small text-muted"><?= e($e['ip_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>
