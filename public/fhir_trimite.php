<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idMedic = currentMedicId();
$idConsultatie = (int)($_GET['id_consultatie'] ?? 0);
$consultatie = null;
$pacient = null;
$sent = false;
$error = '';
$fhirPreview = '';

// Incarca consultatia daca e selectata
if ($idConsultatie) {
    $consultatie = ConsultatieRepo::findById($idConsultatie);
    if ($consultatie) {
        $pacient = PacientRepo::findById($consultatie['id_pacient']);
        $medic = MedicRepo::findById($consultatie['id_medic']);
        
        // Genereaza preview FHIR
        $fhirMessage = [
            'resourceType' => 'DiagnosticReport',
            'id' => 'scrisoare-vc-' . $idConsultatie,
            'status' => 'final',
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '34133-9', 'display' => 'Summarization of episode note']]],
            'subject' => [
                'reference' => 'Patient/' . $pacient['id'],
                'display' => PacientRepo::fullName($pacient)
            ],
            'effectiveDateTime' => date('c', strtotime($consultatie['data_consultatie'])),
            'issued' => date('c'),
            'performer' => [[
                'reference' => 'Practitioner/' . ($medic['id_medic'] ?? ''),
                'display' => 'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''))
            ]],
            'conclusion' => trim(
                'Diagnostic: ' . ($consultatie['diagnostic'] ?? '') . '. ' .
                'Simptome: ' . ($consultatie['simptome'] ?? '') . '. ' .
                'Retete: ' . ($consultatie['retete'] ?? '')
            ),
            'conclusionCode' => [[
                'coding' => [[
                    'system' => 'http://hl7.org/fhir/sid/icd-10',
                    'code' => explode(' ', $consultatie['diagnostic'] ?? '')[0],
                    'display' => $consultatie['diagnostic'] ?? ''
                ]]
            ]]
        ];
        $fhirPreview = json_encode($fhirMessage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

// Trimitere efectiva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $urlDest = trim($_POST['url_destinatie'] ?? '');
    $idCons = (int)($_POST['id_consultatie'] ?? 0);
    
    if (!$urlDest) {
        $error = 'Introdu URL-ul sistemului colegei.';
    } elseif (!$idCons) {
        $error = 'Selecteaza o consultatie.';
    } else {
        // Trimitem prin API-ul nostru intern
        $postData = json_encode([
            'id_consultatie' => $idCons, 
            'url_destinatie' => $urlDest,
            'nume_destinatar' => trim($_POST['nume_destinatar'] ?? 'Medic de familie'),
            'api_key' => trim($_POST['api_key'] ?? '')
        ]);
        
        $ch = curl_init('http://localhost:8000/api/fhir_send.php');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && ($result['success'] ?? false)) {
            logCurrentUserAction('SEND_FHIR', 'Mesaje', $idCons, 'Scrisoare FHIR trimisa catre: ' . $urlDest);
            flash('success', 'Scrisoarea medicala FHIR a fost trimisa cu succes!');
            redirect(url('mesaje_hl7.php'));
        } elseif ($httpCode >= 200 && $httpCode < 300) {
            // Răspuns HTTP OK dar fără câmp "success" — tot e bine
            logCurrentUserAction('SEND_FHIR', 'Mesaje', $idCons, 'Scrisoare FHIR trimisa catre: ' . $urlDest . ' (HTTP ' . $httpCode . ')');
            flash('success', 'Scrisoarea medicala FHIR a fost trimisa cu succes! (HTTP ' . $httpCode . ')');
            redirect(url('mesaje_hl7.php'));
        } else {
            $error = 'Eroare la trimitere: ' . ($result['error'] ?? $response ?? 'Raspuns necunoscut');
            // Salvam oricum mesajul local
            if (!isMockMode() && $fhirPreview) {
                $medic = MedicRepo::findById($idMedic);
                $numeMedic = $medic ? 'Dr. ' . $medic['nume'] . ' ' . $medic['prenume'] : 'Medic';
                try {
                    $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, GETDATE())');
                    $stmt->execute(['Scrisoare medicala', $numeMedic, trim($_POST['nume_destinatar'] ?? 'Medic de familie'), $fhirPreview]);
                } catch (\PDOException $e) {}
            }
            flash('warning', 'Mesajul a fost salvat local, dar trimiterea catre sistemul extern a esuat. Puteti reincerca.');
        }
    }
}

// Lista consultatii pentru dropdown
$consultatii = ConsultatieRepo::findByMedic($idMedic);

renderHeader('Trimite scrisoare FHIR', 'mesaje_hl7');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('mesaje_hl7.php') ?>">Mesaje HL7</a> / Trimite scrisoare
        </div>
        <h1>📤 Trimite scrisoare medicala FHIR</h1>
    </div>
</div>

<div class="flash flash-info">
    Aceasta pagina genereaza o scrisoare medicala in format HL7 FHIR (DiagnosticReport) 
    pe baza unei consultatii existente si o trimite catre sistemul medicului de familie (cealalta echipa).
</div>

<form method="POST" action="">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
    
    <div class="card">
        <div class="card-header"><h3>1. Selecteaza consultatia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Consultatie <span class="required">*</span></label>
                <select name="id_consultatie" class="form-control" required onchange="window.location='?id_consultatie='+this.value">
                    <option value="">— Alege o consultatie —</option>
                    <?php foreach ($consultatii as $c): 
                        $p = PacientRepo::findById($c['id_pacient']);
                        $numePac = $p ? PacientRepo::fullName($p) : 'Pacient #' . $c['id_pacient'];
                    ?>
                        <option value="<?= $c['id'] ?>" <?= $idConsultatie == $c['id'] ? 'selected' : '' ?>>
                            <?= e(formatDate($c['data_consultatie'])) ?> — <?= e($numePac) ?> — <?= e(truncate($c['diagnostic'] ?? '', 40)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    
    <?php if ($consultatie && $pacient): ?>
    <div class="card mt-4">
        <div class="card-header"><h3>2. Preview mesaj FHIR</h3></div>
        <div class="card-body">
            <div class="d-flex gap-3 mb-4" style="flex-wrap:wrap;">
                <div><strong>Pacient:</strong> <?= e(PacientRepo::fullName($pacient)) ?></div>
                <div><strong>Diagnostic:</strong> <?= e($consultatie['diagnostic'] ?? '-') ?></div>
                <div><strong>Data consultatie:</strong> <?= e(formatDate($consultatie['data_consultatie'])) ?></div>
            </div>
            
            <details open>
                <summary style="cursor:pointer; font-weight:bold; margin-bottom: 8px;">Mesaj FHIR generat (JSON)</summary>
                <pre style="background: #f5f5f5; padding: 16px; border-radius: 8px; font-size: 0.8rem; overflow-x: auto; max-height: 400px;"><?= e($fhirPreview) ?></pre>
            </details>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header"><h3>3. Destinatie</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">URL-ul endpoint-ului colegei <span class="required">*</span></label>
                <input type="url" name="url_destinatie" class="form-control" required
                       value="<?= e($_POST['url_destinatie'] ?? 'https://dandelion-bundle-raisin.ngrok-free.dev/api/hl7messages/receive') ?>"
                       placeholder="http://ip-colega:port/api/fhir_receive.php">
            </div>
            
            <div class="form-group">
                <label class="form-label">API Key (dacă e necesar)</label>
                <input type="text" name="api_key" class="form-control"
                       value="<?= e($_POST['api_key'] ?? 'aeh-demo-2026') ?>"
                       placeholder="ex: aeh-demo-2026">
            </div>
            
            <div class="form-group">
                <label class="form-label">Numele medicului de familie destinatar <span class="required">*</span></label>
                <input type="text" name="nume_destinatar" class="form-control" required
                       value="<?= e($_POST['nume_destinatar'] ?? '') ?>"
                       placeholder="ex: Dr. Marinescu Stefan">
            </div>
            
            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <input type="hidden" name="id_consultatie" value="<?= $idConsultatie ?>">
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">📤 Trimite scrisoarea medicala FHIR</button>
        <a href="<?= url('mesaje_hl7.php') ?>" class="btn">Renunta</a>
    </div>
    <?php endif; ?>
</form>

<?php renderFooter(); ?>