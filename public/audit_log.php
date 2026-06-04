<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

// Filtre
$filtruUtilizator = trim($_GET['utilizator'] ?? '');
$filtruActiune = trim($_GET['actiune'] ?? '');
$filtruEntitate = trim($_GET['entitate'] ?? '');

// Construiește query cu filtre
$entries = [];
if (!isMockMode()) {
    try {
        $sql = "SELECT TOP 500 a.*, u.email, u.rol 
                FROM AuditLog a 
                LEFT JOIN Utilizatori u ON a.id_utilizator = u.id 
                WHERE 1=1";
        $params = [];
        
        if ($filtruUtilizator !== '') {
            $sql .= " AND u.email LIKE ?";
            $params[] = '%' . $filtruUtilizator . '%';
        }
        if ($filtruActiune !== '') {
            $sql .= " AND a.actiune = ?";
            $params[] = $filtruActiune;
        }
        if ($filtruEntitate !== '') {
            $sql .= " AND a.entitate = ?";
            $params[] = $filtruEntitate;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll();
    } catch (\PDOException $e) {
        $entries = [];
    }
}

// Extrage valori unice pentru dropdown-uri
$actiuniUnice = [];
$entitatiUnice = [];
foreach ($entries as $e) {
    if (!empty($e['actiune'])) $actiuniUnice[$e['actiune']] = true;
    if (!empty($e['entitate'])) $entitatiUnice[$e['entitate']] = true;
}
// Adaugă și acțiunile standard care poate nu apar încă
foreach (['LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'ARCHIVE', 'RESTORE', 'DELETE', 'REGISTER', 'LOGIN_FAILED', 'CHANGE_PASSWORD'] as $a) {
    $actiuniUnice[$a] = true;
}
// Adaugă toate entitățile posibile
foreach (['Utilizatori', 'Pacient', 'Consultatii', 'Alarme', 'Recomandari', 'Activitati', 'Dispozitive', 'Mesaje', 'PraguriPacient'] as $ent) {
    $entitatiUnice[$ent] = true;
}
ksort($actiuniUnice);
ksort($entitatiUnice);

renderHeader('Jurnal acțiuni', 'audit_log');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Sistem</div>
        <h1>Jurnal acțiuni (audit log)</h1>
    </div>
    <div class="page-actions">
        <span class="badge badge-secondary"><?= count($entries) ?> înregistrări</span>
    </div>
</div>

<div class="flash flash-info">
    Acest jurnal înregistrează toate acțiunile importante din sistem conform criteriilor 
    EuroRec (GS002182, GS002184, GS002198). Înregistrările sunt imutabile.
</div>

<!-- Filtre -->
<div class="card mb-4">
    <div class="card-header"><h3>🔍 Filtrează</h3></div>
    <div class="card-body">
        <form method="GET" action="" class="d-flex gap-3 flex-wrap align-center">
            <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
                <label class="form-label text-small">Utilizator</label>
                <input type="text" name="utilizator" class="form-control" 
                       value="<?= e($filtruUtilizator) ?>" placeholder="Caută după email...">
            </div>
            <div class="form-group" style="flex:1; min-width:150px; margin-bottom:0;">
                <label class="form-label text-small">Acțiune</label>
                <select name="actiune" class="form-control">
                    <option value="">— Toate —</option>
                    <?php foreach ($actiuniUnice as $a => $_): ?>
                        <option value="<?= e($a) ?>" <?= $filtruActiune === $a ? 'selected' : '' ?>><?= e($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex:1; min-width:150px; margin-bottom:0;">
                <label class="form-label text-small">Entitate</label>
                <select name="entitate" class="form-control">
                    <option value="">— Toate —</option>
                    <?php foreach ($entitatiUnice as $ent => $_): ?>
                        <option value="<?= e($ent) ?>" <?= $filtruEntitate === $ent ? 'selected' : '' ?>><?= e($ent) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0; padding-top: 1.4rem;">
                <button type="submit" class="btn btn-primary">Filtrează</button>
                <?php if ($filtruUtilizator || $filtruActiune || $filtruEntitate): ?>
                    <a href="<?= url('audit_log.php') ?>" class="btn btn-outline">Resetează</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel audit -->
<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Niciun eveniment <?= ($filtruUtilizator || $filtruActiune || $filtruEntitate) ? 'găsit cu filtrele selectate' : 'înregistrat încă' ?></h3>
                <p><?= ($filtruUtilizator || $filtruActiune || $filtruEntitate) ? 'Încearcă alte filtre sau resetează.' : 'Acțiunile vor apărea aici pe măsură ce sunt efectuate.' ?></p>
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
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td class="text-small"><?= e(formatDateTime($e['created_at'])) ?></td>
                            <td class="text-small">
                                <?php if (!empty($e['email'])): ?>
                                    <?= e($e['email']) ?>
                                    <br><span class="badge <?= strtolower($e['rol'] ?? '') === 'medic' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size:0.65rem;"><?= e(strtoupper($e['rol'] ?? '')) ?></span>
                                <?php else: ?>
                                    <em>anonim</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $action = $e['actiune'] ?? '';
                                $cls = match($action) {
                                    'LOGIN', 'CREATE', 'REGISTER' => 'badge-success',
                                    'LOGOUT', 'VIEW' => 'badge-secondary',
                                    'UPDATE', 'CHANGE_PASSWORD', 'RESTORE' => 'badge-warning',
                                    'DELETE', 'ARCHIVE', 'LOGIN_FAILED' => 'badge-danger',
                                    default => 'badge-primary',
                                };
                                ?>
                                <span class="badge <?= $cls ?>"><?= e($action) ?></span>
                            </td>
                            <td class="text-small">
                                <?= e($e['entitate'] ?? '') ?>
                                <?php if (!empty($e['entitate_id'])): ?>
                                    #<?= e($e['entitate_id']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-small"><?= e(truncate($e['detalii'] ?? '', 80)) ?></td>
                            <td class="text-small text-muted"><?= e($e['ip_address'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>