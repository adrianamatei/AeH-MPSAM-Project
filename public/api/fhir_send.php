<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('Doar POST.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$idConsultatie = (int)($input['id_consultatie'] ?? 0);
$urlDestinatie = trim($input['url_destinatie'] ?? '');
$numeDestinatar = trim($input['nume_destinatar'] ?? 'Medic de familie');
$apiKey = trim($input['api_key'] ?? '');

if (!$idConsultatie || !$urlDestinatie) {
    apiError('id_consultatie si url_destinatie sunt obligatorii.');
}

$consultatie = ConsultatieRepo::findById($idConsultatie);
if (!$consultatie) apiError('Consultatie negasita.');

$pacient = PacientRepo::findById($consultatie['id_pacient']);
$medic = MedicRepo::findById($consultatie['id_medic']);

// Evităm erorile PHP dacă pacientul/medicul nu sunt găsiți (fallback curat)
$idPacient = is_array($pacient) ? ($pacient['id'] ?? '') : '';
$numePacientComplet = is_array($pacient) ? trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? '')) : 'Pacient Necunoscut';

// ATENȚIE: Verifică dacă în baza ta de date medicul are 'id' sau 'id_medic' ca Primary Key
$idMedicReal = is_array($medic) ? ($medic['id'] ?? $medic['id_medic'] ?? '') : '';
$numeMedicComplet = is_array($medic) ? trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? '')) : 'Medic Necunoscut';

// Construim mesajul FHIR DiagnosticReport
$fhirMessage = [
    'resourceType' => 'DiagnosticReport',
    'id' => 'scrisoare-vc-' . $idConsultatie,
    'status' => 'final',
    'code' => [
        'coding' => [[
            'system' => 'http://loinc.org',
            'code' => '34133-9',
            'display' => 'Summarization of episode note'
        ]]
    ],
    'subject' => [
        'reference' => 'Patient/' . $idPacient,
        'display' => $numePacientComplet
    ],
    'effectiveDateTime' => date('c', strtotime($consultatie['data_consultatie'] ?? 'now')),
    'issued' => date('c'),
    'performer' => [[
        'reference' => 'Practitioner/' . $idMedicReal,
        'display' => 'Dr. ' . $numeMedicComplet
    ]],
    'conclusion' => trim(
        'Diagnostic: ' . ($consultatie['diagnostic'] ?? 'Nespecificat') . '. ' .
        'Simptome: ' . ($consultatie['simptome'] ?? '-') . '. ' .
        'Retete: ' . ($consultatie['retete'] ?? '-')
    ),
    'conclusionCode' => [[
        'coding' => [[
            'system' => 'http://hl7.org/fhir/sid/icd-10',
            'code' => explode(' ', $consultatie['diagnostic'] ?? '000')[0],
            'display' => $consultatie['diagnostic'] ?? 'Nespecificat'
        ]]
    ]]
];

$jsonBody = json_encode($fhirMessage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Trimitem catre sistemul colegei
$headers = ['Content-Type: application/fhir+json', 'Accept: application/json'];
if ($apiKey) {
    $headers[] = 'X-Api-Key: ' . $apiKey;
}

$ch = curl_init($urlDestinatie);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonBody,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    // CREȘTEM TIMEOUT-UL la 30 de secunde pentru a asigura răspunsurile via ngrok
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Salvam si la noi in Mesaje
if (!isMockMode()) {
    try {
        $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, GETDATE())');
        $stmt->execute([
            'Scrisoare medicala',
            'Dr. ' . $numeMedicComplet,
            $numeDestinatar,
            $jsonBody
        ]);
    } catch (\PDOException $e) {
        error_log("Eroare la salvarea locală a mesajului: " . $e->getMessage());
    }
}

if ($error) {
    apiResponse(['success' => false, 'error' => 'Timeout sau eroare rețea: ' . $error, 'fhir_message' => $fhirMessage], 502);
} else {
    apiResponse([
        'success' => true,
        'message' => 'Scrisoare medicala FHIR trimisa cu succes.',
        'http_code' => $httpCode,
        'response' => $response,
        'fhir_message' => $fhirMessage
    ]);
}