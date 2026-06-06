<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$entitate = $_GET['entitate'] ?? '';
$entitateId = (int)($_GET['id'] ?? 0);

$versiuni = [];
$titlu = 'Istoric versiuni';
$numePacient = '';

if ($entitate && $entitateId) {
    $versiuni = VersionHistoryRepo::findByEntity($entitate, $entitateId);
    
    // Rezolvă numele pacientului
    if ($entitate === 'Pacient') {
        $p = PacientRepo::findById($entitateId);
        if ($p) $numePacient = PacientRepo::fullName($p);
    } elseif (in_array($entitate, ['Consultatii', 'Alarme', 'Recomandari', 'Medicatie', 'Activitati'])) {
        $dateVechi = json_decode($versiuni[0]['date_vechi'] ?? '{}', true);
        if (!empty($dateVechi['id_pacient'])) {
            $p = PacientRepo::findById((int)$dateVechi['id_pacient']);
            if ($p) $numePacient = PacientRepo::fullName($p);
        }
    }
    
    $titlu = $entitate . ' #' . $entitateId;
    if ($numePacient) $titlu .= ' — ' . $numePacient;
} else {
    $versiuni = VersionHistoryRepo::recent(100);
}

// Pentru lista generală, rezolvăm numele pacientului pentru fiecare versiune
function resolvePatientName($v) {
    $entitate = $v['entitate'] ?? '';
    $entitateId = (int)($v['entitate_id'] ?? 0);
    
    if ($entitate === 'Pacient') {
        $p = PacientRepo::findById($entitateId);
        return $p ? PacientRepo::fullName($p) : '';
    }
    
    $dateVechi = json_decode($v['date_vechi'] ?? '{}', true);
    if (!empty($dateVechi['id_pacient'])) {
        $p = PacientRepo::findById((int)$dateVechi['id_pacient']);
        return $p ? PacientRepo::fullName($p) : '';
    }
    
    // Încearcă din date_noi
    $dateNoi = json_decode($v['date_noi'] ?? '{}', true);
    if (!empty($dateNoi['id_pacient'])) {
        $p = PacientRepo::findById((int)$dateNoi['id_pacient']);
        return $p ? PacientRepo::fullName($p) : '';
    }
    
    return '';
}

// Câmpuri pe care le excludem din diff (nu sunt relevante)
$campuriExcluse = ['id', 'id_medic', 'id_utilizator', 'id_pacient', 'is_deleted', 'cnp', 'CNP'];

// Traduceri câmpuri pentru afișare
$traduceri = [
    'nume' => 'Nume', 'prenume' => 'Prenume', 'varsta' => 'Vârstă', 'sex' => 'Sex',
    'data_nasterii' => 'Data nașterii', 'strada' => 'Stradă', 'oras' => 'Oraș',
    'judet' => 'Județ', 'telefon' => 'Telefon', 'profesie' => 'Profesie',
    'loc_de_munca' => 'Loc de muncă', 'istoric_medical' => 'Istoric medical',
    'alergii' => 'Alergii', 'email' => 'Email',
    'tip_alarma' => 'Tip alarmă', 'valoare_declansatoare' => 'Valoare declanșare',
    'prag_min' => 'Prag minim', 'prag_max' => 'Prag maxim',
    'mesaj' => 'Mesaj', 'durata_persistenta' => 'Durată persistență',
    'tip_recomandare' => 'Tip recomandare', 'indicatii' => 'Indicații',
    'diagnostic' => 'Diagnostic', 'simptome' => 'Simptome',
    'motiv_prezentare' => 'Motiv prezentare', 'retete' => 'Rețete',
    'trimiteri' => 'Trimiteri', 'data_consultatie' => 'Data consultație',
    'produs' => 'Produs', 'doza' => 'Doză', 'posologie' => 'Posologie',
    'forma_prezentare' => 'Formă prezentare', 'status' => 'Status',
    'data_inceput' => 'Data început', 'data_sfarsit' => 'Data sfârșit',
    'observatii' => 'Observații', 'moment_declansare' => 'Moment declanșare',
];

function translateField($field) {
    global $traduceri;
    return $traduceri[$field] ?? ucfirst(str_replace('_', ' ', $field));
}

renderHeader('Istoric versiuni', 'versiuni');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('audit_log.php') ?>">Sistem</a> / Istoric versiuni
        </div>
        <h1>Istoric versiuni<?= $numePacient ? ': ' . e($numePacient) : '' ?></h1>
    </div>
    <div class="page-actions">
        <span class="badge badge-secondary"><?= count($versiuni) ?> versiuni</span>
    </div>
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

    <!-- Tabel rezumat -->
    <div class="card">
        <div class="card-header"><h3>Toate modificările</h3></div>
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Data</th>
                        <th>Utilizator</th>
                        <?php if (!$entitate): ?><th>Entitate</th><?php endif; ?>
                        <th>Pacient</th>
                        <th>Acțiune</th>
                        <th>Ce s-a modificat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versiuni as $i => $v): 
                        $dateVechi = json_decode($v['date_vechi'] ?? '{}', true) ?: [];
                        $dateNoi = json_decode($v['date_noi'] ?? '{}', true) ?: [];
                        $pacientNume = resolvePatientName($v);
                        
                        $clsActiune = match($v['actiune']) {
                            'UPDATE' => 'badge-warning',
                            'ARCHIVE' => 'badge-danger',
                            'STOP' => 'badge-secondary',
                            default => 'badge-primary',
                        };
                        
                        // Calculează câmpurile modificate
                        $campuriModificate = [];
                        if ($v['actiune'] === 'UPDATE' && !empty($dateVechi) && !empty($dateNoi)) {
                            foreach ($dateNoi as $key => $val) {
                                if (in_array($key, $campuriExcluse)) continue;
                                $oldVal = $dateVechi[$key] ?? null;
                                if ($oldVal !== null && (string)$oldVal !== (string)$val && $val !== '') {
                                    $campuriModificate[] = translateField($key);
                                }
                            }
                        }
                        $rezumatModificari = !empty($campuriModificate) ? implode(', ', $campuriModificate) : '—';
                    ?>
                        <tr style="cursor:pointer;" onclick="toggleDetail(<?= $i ?>)">
                            <td class="text-muted"><?= count($versiuni) - $i ?></td>
                            <td class="text-small"><?= e(formatDateTime($v['created_at'])) ?></td>
                            <td class="text-small"><?= e($v['email'] ?? 'anonim') ?></td>
                            <?php if (!$entitate): ?>
                                <td><span class="badge badge-secondary"><?= e($v['entitate']) ?> #<?= e($v['entitate_id']) ?></span></td>
                            <?php endif; ?>
                            <td class="text-bold"><?= e($pacientNume ?: '—') ?></td>
                            <td><span class="badge <?= $clsActiune ?>"><?= e($v['actiune']) ?></span></td>
                            <td class="text-small"><?= e($rezumatModificari) ?></td>
                        </tr>
                        
                        <!-- Detalii expandabile -->
                        <tr id="detail-<?= $i ?>" style="display:none;">
                            <td colspan="<?= $entitate ? '6' : '7' ?>" style="background: #fafafa; padding: 16px 24px;">
                                <?php if ($v['actiune'] === 'UPDATE' && !empty($dateVechi) && !empty($dateNoi)):
                                    $modificari = [];
                                    foreach ($dateNoi as $key => $val) {
                                        if (in_array($key, $campuriExcluse)) continue;
                                        $oldVal = $dateVechi[$key] ?? null;
                                        if ($oldVal !== null && (string)$oldVal !== (string)$val && $val !== '') {
                                            $modificari[$key] = ['vechi' => $oldVal, 'nou' => $val];
                                        }
                                    }
                                ?>
                                    <?php if (!empty($modificari)): ?>
                                        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                                            <thead>
                                                <tr style="background:#eee;">
                                                    <th style="padding:8px 12px; text-align:left; border:1px solid #ddd;">Câmp</th>
                                                    <th style="padding:8px 12px; text-align:left; border:1px solid #ddd;">Valoare veche</th>
                                                    <th style="padding:8px 12px; text-align:center; border:1px solid #ddd; width:30px;">→</th>
                                                    <th style="padding:8px 12px; text-align:left; border:1px solid #ddd;">Valoare nouă</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($modificari as $camp => $vals): ?>
                                                    <tr>
                                                        <td style="padding:6px 12px; border:1px solid #ddd; font-weight:600;"><?= e(translateField($camp)) ?></td>
                                                        <td style="padding:6px 12px; border:1px solid #ddd; background:#fff0f0; color:#b00;"><?= e(truncate((string)$vals['vechi'], 120)) ?></td>
                                                        <td style="padding:6px 12px; border:1px solid #ddd; text-align:center;">→</td>
                                                        <td style="padding:6px 12px; border:1px solid #ddd; background:#f0fff0; color:#060;"><?= e(truncate((string)$vals['nou'], 120)) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted">Nicio diferență detectată în câmpurile vizibile.</p>
                                    <?php endif; ?>
                                    
                                <?php elseif ($v['actiune'] === 'ARCHIVE' || $v['actiune'] === 'STOP'): ?>
                                    <p style="margin-bottom:8px; color:#666;">
                                        <strong><?= $v['actiune'] === 'ARCHIVE' ? '📦 Înregistrare arhivată' : '⏹ Tratament oprit' ?></strong>
                                        — snapshot complet salvat la momentul acțiunii:
                                    </p>
                                    <details>
                                        <summary style="cursor:pointer; color: var(--primary); font-size:0.85rem;">Arată datele complete</summary>
                                        <pre style="background:#f5f5f5; padding:12px; border-radius:6px; font-size:0.75rem; overflow-x:auto; margin-top:8px;"><?= e(json_encode($dateVechi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    </details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<script>
function toggleDetail(i) {
    var el = document.getElementById('detail-' + i);
    el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php renderFooter(); ?>