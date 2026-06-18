<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(['status' => 'ok', 'message' => 'FHIR endpoint activ. Trimite POST cu JSON FHIR.']);
}

$body = file_get_contents('php://input');
$json = json_decode($body, true);

if (!$json || !isset($json['resourceType'])) {
    apiError('JSON FHIR invalid. Lipseste resourceType.', 400);
}

$tipMesaj = 'Trimitere';
$sursa = 'Sistem extern';
$destinatie = 'Vital Cares';
$cnpPacient = '';
$numePacient = '';
$detaliiTrimitere = '';

// Parsare Bundle FHIR (formatul colegei)
if ($json['resourceType'] === 'Bundle' && !empty($json['entry'])) {
    
    foreach ($json['entry'] as $entry) {
        $resource = $entry['resource'] ?? [];
        $resType = $resource['resourceType'] ?? '';
        
        // Extrage sursa din MessageHeader
        if ($resType === 'MessageHeader') {
            $sursa = $resource['source']['name'] ?? 'Sistem extern';
            $destinatie = $resource['destination'][0]['name'] ?? 'Vital Cares';
            
            $eventCode = $resource['eventCoding']['code'] ?? '';
            if ($eventCode === 'R01' || str_contains(($resource['eventCoding']['display'] ?? ''), 'Referral')) {
                $tipMesaj = 'Trimitere';
            }
        }
        
        // Extrage datele pacientului
        if ($resType === 'Patient') {
            $cnpPacient = '';
            
            // Parsare corectată și sigură pentru CNP
            if (!empty($resource['identifier']) && is_array($resource['identifier'])) {
                foreach ($resource['identifier'] as $ident) {
                    if (isset($ident['value']) && $ident['value'] !== '') {
                        $cnpPacient = trim($ident['value']);
                        break;
                    }
                }
            }
            
            $family = $resource['name'][0]['family'] ?? 'Necunoscut';
            $given = $resource['name'][0]['given'][0] ?? '';
            $numePacient = trim($family . ' ' . $given);
            $gender = $resource['gender'] ?? '';
            $birthDate = $resource['birthDate'] ?? null;
            
            // Verificăm și creăm pacientul (fără a mai bloca dacă lipsește temporar CNP-ul)
            if (!isMockMode()) {
                try {
                    $pacientExistent = false;
                    
                    // Căutăm pacientul doar dacă am reușit să extragem un CNP
                    if (!empty($cnpPacient)) {
                        $stmt = db()->prepare('SELECT id, id_medic, nume, prenume FROM Pacient WHERE CNP = ?');
                        $stmt->execute([$cnpPacient]);
                        $pacientExistent = $stmt->fetch();
                    }
                    
                    if (!$pacientExistent) {
                        // Creăm pacientul fără medic — un cardiolog îl va asocia manual
                        $sex = ($gender === 'male') ? 'Masculin' : (($gender === 'female') ? 'Feminin' : '');
                        $varsta = 0;
                        if ($birthDate) {
                            $varsta = (int)date_diff(date_create($birthDate), date_create('now'))->y;
                        }
                        
                        $cnpDeInserat = !empty($cnpPacient) ? $cnpPacient : null;
                        
                        $stmt = db()->prepare('INSERT INTO Pacient (nume, prenume, CNP, varsta, sex, data_nasterii) VALUES (?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$family, $given, $cnpDeInserat, $varsta, $sex, $birthDate]);
                    }
                } catch (\PDOException $e) {
                    // Salvăm eroarea SQL pentru a o vedea direct în interfață
                    error_log('EROARE DB PACIENT FHIR: ' . $e->getMessage());
                    $detaliiTrimitere .= "\n\n[EROARE SQL LA CREAREA PACIENTULUI: " . $e->getMessage() . "]";
                }
            }
        }
        
        // Extrage detaliile din ServiceRequest
        if ($resType === 'ServiceRequest') {
            $motiv = $resource['reasonCode'][0]['text'] ?? '';
            $nota = $resource['note'][0]['text'] ?? '';
            $specialitate = $resource['code']['text'] ?? '';
            $detaliiRequest = trim("Specialitate: $specialitate. Motiv: $motiv. Note: $nota");
            // Adaugăm la detalii fără să suprascriem posibilele erori SQL
            $detaliiTrimitere = $detaliiRequest . $detaliiTrimitere; 
        }
    }
    
} elseif ($json['resourceType'] === 'ServiceRequest') {
    // Format simplu (fără Bundle)
    $tipMesaj = 'Trimitere';
    if (isset($json['requester']['display'])) $sursa = $json['requester']['display'];
    if (isset($json['performer'][0]['display'])) $destinatie = $json['performer'][0]['display'];
    
} elseif ($json['resourceType'] === 'DiagnosticReport') {
    $tipMesaj = 'Scrisoare medicala';
    if (isset($json['performer'][0]['display'])) $sursa = $json['performer'][0]['display'];
}

// Salvăm mesajul în baza de date
if (!isMockMode()) {
    try {
        $continut = $body;
        if ($detaliiTrimitere || $numePacient) {
            $continut = "Pacient: $numePacient (CNP: " . ($cnpPacient ?: 'Necunoscut') . ")\n$detaliiTrimitere\n\n--- FHIR JSON ---\n$body";
        }
        
        $stmt = db()->prepare('INSERT INTO Mesaje (tip_mesaj, sursa, destinatie, continut, moment_transmitere) VALUES (?, ?, ?, ?, GETDATE())');
        $stmt->execute([$tipMesaj, $sursa, $destinatie, $continut]);
        $id = (int)db()->lastInsertId();
        
        apiResponse([
            'success' => true,
            'message' => 'Mesaj FHIR primit si stocat cu succes.',
            'id' => $id,
            'resourceType' => $json['resourceType'],
            'tip_mesaj' => $tipMesaj,
            'pacient' => $numePacient,
            'cnp' => $cnpPacient
        ], 201);
    } catch (\PDOException $e) {
        error_log('EROARE DB SALVARE MESAJ FHIR: ' . $e->getMessage());
        apiError('Eroare la salvare: ' . $e->getMessage(), 500);
    }
} else {
    apiResponse(['success' => true, 'message' => 'Mesaj primit (mod mock).'], 201);
}