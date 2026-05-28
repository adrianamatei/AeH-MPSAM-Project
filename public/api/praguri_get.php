<?php
require_once __DIR__ . '/_bootstrap.php';

$idPacient = (int)($_GET['id_pacient'] ?? 0);
if (!$idPacient) apiError('Parametrul id_pacient este obligatoriu.');

$praguri = PraguriRepo::findByPacient($idPacient);
apiResponse(['success' => true, 'data' => $praguri]);