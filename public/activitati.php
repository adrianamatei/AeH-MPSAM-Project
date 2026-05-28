<?php
require_once __DIR__ . '/../app/config.php';
requireLogin();

$user = currentUser();

if ($user['rol'] === 'pacient') {
    $idPacient = currentPacientId();
    $activitati = ActivitateRepo::findByPacient($idPacient);
    
    // Toggle finalizare
    if (($_POST['action'] ?? '') === 'toggle') {
        requireCsrf();
        $id = (int)$_POST['id'];
        $act = ActivitateRepo::findById($id);
        if ($act && $act['id_pacient'] == $idPacient) {
            ActivitateRepo::marcheazaFinalizata($id, $act['este_finalizata'] ? 0 : 1);
            flash('success', 'Status actualizat.');
        }
        redirect(url('activitati.php'));
    }
} else {
    // Medic: vede activitățile tuturor pacienților lui
    $idPacientFilter = (int)($_GET['id_pacient'] ?? 0);
    if ($idPacientFilter && medicCanAccessPacient($idPacientFilter)) {
        $activitati = ActivitateRepo::findByPacient($idPacientFilter);
    } else {
        $pacientiMedic = PacientRepo::findByMedic(currentMedicId());
        $activitati = [];
        foreach ($pacientiMedic as $p) {
            $activitati = array_merge($activitati, ActivitateRepo::findByPacient($p['id']));
        }
        usort($activitati, fn($a, $b) => strcmp($b['data_programata'], $a['data_programata']));
    }
}

renderHeader('Activități', 'activitati');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">Recomandări</div>
        <h1><?= $user['rol'] === 'pacient' ? 'Activitățile mele' : 'Activități programate' ?></h1>
    </div>
    <?php if ($user['rol'] === 'medic'): ?>
    <div class="page-actions">
        <a href="<?= url('activitate_adauga.php') ?>" class="btn btn-primary">+ Activitate nouă</a>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($activitati)): ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>Nicio activitate programată</h3>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ora</th>
                        <?php if ($user['rol'] === 'medic'): ?>
                            <th>Pacient</th>
                        <?php endif; ?>
                        <th>Activitate</th>
                        <th>Descriere</th>
                        <th>Status</th>
                        <?php if ($user['rol'] === 'pacient'): ?>
                            <th class="actions">Acțiuni</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activitati as $a):
                        $pacient = PacientRepo::findById($a['id_pacient']);
                    ?>
                        <tr>
                            <td class="text-small"><?= e(formatDate($a['data_programata'])) ?></td>
                            <td><?= e($a['ora_programata']) ?></td>
                            <?php if ($user['rol'] === 'medic'): ?>
                                <td><?= e(PacientRepo::fullName($pacient)) ?></td>
                            <?php endif; ?>
                            <td class="text-bold"><?= e($a['nume_activitate']) ?></td>
                            <td class="text-small text-muted"><?= e(truncate($a['descriere'], 60)) ?></td>
                            <td>
                                <?php if ($a['este_finalizata']): ?>
                                    <span class="badge badge-success">Finalizată</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">De făcut</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($user['rol'] === 'pacient'): ?>
                                <td class="actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= e($a['id_activitate']) ?>">
                                        <button type="submit" class="btn btn-sm <?= $a['este_finalizata'] ? '' : 'btn-success' ?>">
                                            <?= $a['este_finalizata'] ? 'Reactivează' : '✓ Marchează făcut' ?>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>
