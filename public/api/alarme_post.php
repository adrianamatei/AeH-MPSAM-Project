<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') apiError('Doar metoda POST este acceptată.', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) apiError('Body JSON invalid.');

$data = [
    'id_pacient' => (int)($input['id_pacient'] ?? 0),
    'tip_alarma' => $input['tip_alarma'] ?? '',
    'valoare_declansare' => (float)($input['valoare_declansare'] ?? 0),
    'prag_minim' => (float)($input['prag_min'] ?? 0),
    'prag_maxim' => (float)($input['prag_max'] ?? 0),
    'moment_declansare' => $input['moment_declansare'] ?? date('Y-m-d H:i:s'),
    'durata_persistenta' => (int)($input['durata_persistenta'] ?? 0),
    'mesaj' => $input['mesaj'] ?? '',
];

if (!$data['id_pacient'] || !$data['tip_alarma']) apiError('id_pacient și tip_alarma sunt obligatorii.');

$id = AlarmaRepo::insert($data);
apiResponse(['success' => true, 'id' => $id], 201);