<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Doar metoda POST este acceptată.', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) apiError('Body JSON invalid.');

$required = ['id_pacient', 'tip_parametru', 'valoare'];
foreach ($required as $field) {
    if (empty($input[$field])) apiError("Câmpul {$field} este obligatoriu.");
}

$data = [
    'id_pacient' => (int)$input['id_pacient'],
    'tip_parametru' => $input['tip_parametru'],
    'valoare' => (float)$input['valoare'],
    'unitate_masurata' => $input['unitate_masurata'] ?? '',
    'moment_inregistrare' => $input['moment_inregistrare'] ?? date('Y-m-d H:i:s'),
];

$id = MasuratoriRepo::insert($data);
apiResponse(['success' => true, 'id' => $id], 201);