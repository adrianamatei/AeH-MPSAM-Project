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

if (!$idConsultatie || !$urlDestinatie) {
    apiError('id_consultatie si url_destinatie sunt obligatorii.');
}

$consultatie = ConsultatieRepo::findById($idConsultatie);
if (!$consultatie) apiError('Consultatie negasita.');

$pacient = PacientRepo::findById($consultatie['id_pacient']);
$medic = MedicRepo::findById($consultatie['id_medic']);

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
        'reference' => 'Patient/' . ($pacient['id'] ?? ''),
        'display' => trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? ''))
    ],
    'effectiveDateTime' => date('c', strtotime($consultatie['data_consultatie'] ?? 'now')),
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

$jsonBody = json_encode($fhirMessage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Trimitem catre sistemul colegei
$ch = curl_init($urlDestinatie);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonBody,
    CURLOPT_HTTPHEADER => ['Content-Type: application/fhir+json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Salvam si la noi in Mesaje ca sa apara in interfata
if (!isMockMode()) {
    try {
        $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, GETDATE())');
        $stmt->execute([
            'Scrisoare medicala',
            'Dr. ' . trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? '')),
            $numeDestinatar,
            $jsonBody
        ]);
    } catch (\PDOException $e) {}
}

if ($error) {
    apiResponse(['success' => false, 'error' => 'Nu am putut trimite: ' . $error, 'fhir_message' => $fhirMessage], 502);
} else {
    apiResponse([
        'success' => true,
        'message' => 'Scrisoare medicala FHIR trimisa cu succes.',
        'http_code' => $httpCode,
        'response' => $response,
        'fhir_message' => $fhirMessage
    ]);
}