<?php
require_once __DIR__ . '/_bootstrap.php';

// Primeste mesaje FHIR de la sistemul celeilalte echipe (medic de familie)
// Endpoint: POST /api/fhir_receive.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(['status' => 'ok', 'message' => 'FHIR endpoint activ. Trimite POST cu JSON FHIR.']);
}

$body = file_get_contents('php://input');
$json = json_decode($body, true);

if (!$json || !isset($json['resourceType'])) {
    apiError('JSON FHIR invalid. Lipseste resourceType.', 400);
}

// Determinam tipul mesajului din resourceType
$tipMesaj = 'Trimitere'; // default
if ($json['resourceType'] === 'ServiceRequest') {
    $tipMesaj = 'Trimitere';
} elseif ($json['resourceType'] === 'DiagnosticReport') {
    $tipMesaj = 'Scrisoare medicala';
}

// Extragem sursa si destinatia din FHIR
$sursa = 'Sistem extern';
$destinatie = 'Vital Cares';

if (isset($json['requester']['display'])) {
    $sursa = $json['requester']['display'];
}
if (isset($json['performer'][0]['display'])) {
    $destinatie = $json['performer'][0]['display'];
}

// Salvam in baza de date
if (!isMockMode()) {
    try {
        $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, GETDATE())');
        $stmt->execute([$tipMesaj, $sursa, $destinatie, $body]);
        $id = (int)db()->lastInsertId();
        
        apiResponse([
            'success' => true,
            'message' => 'Mesaj FHIR primit si stocat cu succes.',
            'id' => $id,
            'resourceType' => $json['resourceType'],
            'tip_mesaj' => $tipMesaj
        ], 201);
    } catch (\PDOException $e) {
        apiError('Eroare la salvare: ' . $e->getMessage(), 500);
    }
} else {
    apiResponse(['success' => true, 'message' => 'Mesaj primit (mod mock).'], 201);
}