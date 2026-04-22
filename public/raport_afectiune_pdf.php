<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';

require_login();
$u = auth_user();

$doctorId = (int)$u['id'];
$diagnostic = trim((string)($_GET['diagnostic'] ?? ''));

if ($diagnostic === '') {
  http_response_code(400);
  exit('Diagnostic lipsă');
}

// Query: pacienții doctorului logat care au diagnostic ce conține textul căutat
$sql = "
  SELECT
    p.cnp, p.nume, p.prenume, p.email, p.telefon, p.varsta, p.adresa,
    MAX(c.data_consultatie) AS ultima_consultatie,
    COUNT(*) AS nr_consultatii
  FROM consultatii c
  JOIN pacienti p ON p.cnp = c.cnp_pacient
  WHERE c.doctor_id = :did
    AND LOWER(c.diagnostic) LIKE :diag
  GROUP BY p.cnp, p.nume, p.prenume, p.email, p.telefon, p.varsta, p.adresa
  ORDER BY ultima_consultatie DESC, p.prenume, p.nume
";

$stmt = db()->prepare($sql);
$stmt->execute([
  ':did' => $doctorId,
  ':diag' => '%' . mb_strtolower($diagnostic) . '%',
]);
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 12,
  'margin_right' => 12,
  'margin_top' => 12,
  'margin_bottom' => 12,
]);

$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$mpdf->SetTitle('Fisa afectiune - ' . $diagnostic);

$css = <<<CSS
body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #111; }
h1 { font-size: 16pt; margin: 0 0 8px 0; }
.small { font-size: 9.5pt; color: #555; margin-bottom: 10px; }
.badge { display: inline-block; padding: 2px 8px; border: 1px solid #333; border-radius: 12px; font-size: 9pt; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #333; padding: 6px; vertical-align: top; }
.table th { background: #f2f2f2; }
CSS;

$doctorName = trim(($u['prenume'] ?? '') . ' ' . ($u['nume'] ?? ''));

$html = '';
$html .= '<h1>Fișa unei afecțiuni</h1>';
$html .= '<div class="small">'
      .  'Diagnostic căutat: <span class="badge">' . $h($diagnostic) . '</span><br>'
      .  'Doctor: ' . $h($doctorName) . '<br>'
      .  'Generat la: ' . date('d.m.Y H:i')
      .  '</div>';

if (!$rows) {
  $html .= '<p>Nu există pacienți pentru acest diagnostic la doctorul curent.</p>';
} else {
  $html .= '<table class="table">';
  $html .= '<tr>
      <th style="width:18%;">Pacient</th>
      <th style="width:16%;">CNP</th>
      <th style="width:16%;">Contact</th>
      <th style="width:10%;">Vârstă</th>
      <th style="width:20%;">Adresă</th>
      <th style="width:10%;">Ultima</th>
      <th style="width:10%;">Nr.</th>
    </tr>';

  foreach ($rows as $r) {
    $contact = trim($r['email'] . "\n" . $r['telefon']);
    $html .= '<tr>';
    $html .= '<td>' . $h($r['prenume'] . ' ' . $r['nume']) . '</td>';
    $html .= '<td>' . $h($r['cnp']) . '</td>';
    $html .= '<td>' . nl2br($h($contact)) . '</td>';
    $html .= '<td>' . $h((string)$r['varsta']) . '</td>';
    $html .= '<td>' . $h($r['adresa']) . '</td>';
    $html .= '<td>' . $h($r['ultima_consultatie']) . '</td>';
    $html .= '<td>' . $h((string)$r['nr_consultatii']) . '</td>';
    $html .= '</tr>';
  }
  $html .= '</table>';
}

$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', mb_strtolower($diagnostic));
$mpdf->Output('fisa_afectiune_' . $safeName . '.pdf', 'I');
