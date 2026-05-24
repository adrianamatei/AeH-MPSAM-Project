<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$query = trim($_GET['q'] ?? '');

// Caută sau listează pacienții medicului curent
$pacienti = empty($query) 
    ? PacientRepo::findByMedic($idMedic)
    : PacientRepo::search($query, $idMedic);

// Alarme recente pe pacient (pentru badge)
$alarmeMedic = AlarmaRepo::findByMedic($idMedic);
$pacientiCuAlarme = [];
foreach ($alarmeMedic as $a) {
    if (strtotime($a['moment_declansare']) >= strtotime('-24 hours')) {
        $pacientiCuAlarme[$a['id_pacient']] = true;
    }
}

renderHeader('Pacienți', 'pacienti');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Pacienți</div>
        <h1>Listă pacienți</h1>
    </div>
    <div class="page-actions">
        <a href="<?= url('pacient_adauga.php') ?>" class="btn btn-primary">+ Pacient nou</a>
    </div>
</div>

<!-- Bara căutare -->
<form method="GET" class="search-bar">
    <input type="text" name="q" class="form-control" 
           value="<?= e($query) ?>" 
           placeholder="Caută după nume, prenume sau CNP...">
    <button type="submit" class="btn btn-primary">Caută</button>
    <?php if (!empty($query)): ?>
        <a href="<?= url('pacienti.php') ?>" class="btn">Resetează</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($pacienti)): ?>
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <h3>Niciun pacient găsit</h3>
                <?php if (!empty($query)): ?>
                    <p>Nu există pacienți care să corespundă căutării "<?= e($query) ?>".</p>
                <?php else: ?>
                    <p>Nu ai pacienți alocați. Începe prin a adăuga unul.</p>
                    <a href="<?= url('pacient_adauga.php') ?>" class="btn btn-primary mt-3">+ Adaugă pacient</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nume / Prenume</th>
                        <th>CNP</th>
                        <th>Vârstă</th>
                        <th>Telefon</th>
                        <th>Status</th>
                        <th class="actions">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacienti as $p): 
                        $areAlarma = isset($pacientiCuAlarme[$p['id']]);
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <div class="patient-avatar" style="width:36px; height:36px; font-size:0.9rem;">
                                        <?= e(mb_substr($p['nume'], 0, 1) . mb_substr($p['prenume'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="text-bold">
                                            <a href="<?= url('pacient_detalii.php?id=' . $p['id']) ?>">
                                                <?= e(PacientRepo::fullName($p)) ?>
                                            </a>
                                        </div>
                                        <div class="text-small text-muted"><?= e($p['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($p['cnp']) ?></td>
                            <td><?= e($p['varsta']) ?> ani</td>
                            <td><?= e($p['telefon']) ?></td>
                            <td>
                                <?php if ($areAlarma): ?>
                                    <span class="badge badge-danger">Alertă</span>
                                <?php else: ?>
                                    <span class="badge badge-success">OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= url('pacient_detalii.php?id=' . $p['id']) ?>" 
                                   class="btn btn-sm btn-outline">Detalii</a>
                                <a href="<?= url('pacient_editare.php?id=' . $p['id']) ?>" 
                                   class="btn btn-sm">Editează</a>
                                <a href="<?= url('praguri_pacient.php?id=' . $p['id']) ?>" 
                                   class="btn btn-sm">Praguri</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php if (!empty($pacienti)): ?>
        <div class="card-footer text-small text-muted">
            Total: <?= count($pacienti) ?> pacienți
        </div>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>