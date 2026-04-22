<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';

require_login();

$cnp = trim((string)($_GET['cnp'] ?? ''));
if ($cnp === '') {
  http_response_code(400);
  exit('CNP lipsă');
}

// Pacient
$ps = db()->prepare("SELECT * FROM pacienti WHERE cnp = :cnp");
$ps->execute([':cnp' => $cnp]);
$p = $ps->fetch();
if (!$p) {
  http_response_code(404);
  exit('Pacient inexistent');
}

// Consultații
$cs = db()->prepare("
  SELECT c.*, d.nume AS d_nume, d.prenume AS d_prenume, s.nume AS specialitate
  FROM consultatii c
  JOIN doctori d ON d.id = c.doctor_id
  JOIN specialitati s ON s.id = c.specialitate_id
  WHERE c.cnp_pacient = :cnp
  ORDER BY c.data_consultatie DESC, c.nr_consultatie DESC
");
$cs->execute([':cnp' => $cnp]);
$consultatii = $cs->fetchAll();

require_once __DIR__ . '/../vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 12,
  'margin_right' => 12,
  'margin_top' => 12,
  'margin_bottom' => 12,
]);

// Helpers locale pentru PDF
$h = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$sexText = '';
if (($p['sex'] ?? '') === 'M') $sexText = 'Masculin';
if (($p['sex'] ?? '') === 'F') $sexText = 'Feminin';

$mpdf->SetTitle('Fisa pacient ' . $cnp);

$css = <<<CSS
body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #111; }
h1 { font-size: 18pt; margin: 0 0 10px 0; }
h2 { font-size: 13pt; margin: 14px 0 8px 0; }
.small { font-size: 9.5pt; color: #555; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #333; padding: 6px; vertical-align: top; }
.table th { background: #f2f2f2; }
.badge { display: inline-block; padding: 2px 8px; border: 1px solid #333; border-radius: 10px; font-size: 9pt; }
.hr { height: 1px; background: #333; margin: 10px 0; }
CSS;

$html = '';
$html .= '<h1>Fișa pacient</h1>';
$html .= '<div class="small">Generat la: ' . date('d.m.Y H:i') . '</div>';
$html .= '<div class="hr"></div>';

$html .= '<h2>Date pacient</h2>';
$html .= '<table class="table">';
$html .= '<tr><th style="width:30%;">CNP</th><td>' . $h($p['cnp']) . '</td></tr>';
$html .= '<tr><th>Nume</th><td>' . $h($p['nume'] ?? '') . '</td></tr>';
$html .= '<tr><th>Prenume</th><td>' . $h($p['prenume'] ?? '') . '</td></tr>';
$html .= '<tr><th>Sex</th><td>' . $h($sexText ?: ($p['sex'] ?? '')) . '</td></tr>';
$html .= '<tr><th>Data nașterii</th><td>' . $h($p['data_nasterii']) . '</td></tr>';
$html .= '<tr><th>Vârstă</th><td>' . $h((string)$p['varsta']) . '</td></tr>';
$html .= '<tr><th>Adresă</th><td>' . $h($p['adresa']) . '</td></tr>';
$html .= '<tr><th>Email</th><td>' . $h($p['email']) . '</td></tr>';
$html .= '<tr><th>Telefon</th><td>' . $h($p['telefon']) . '</td></tr>';
$html .= '</table>';

$html .= '<h2>Consultații</h2>';

if (!$consultatii) {
  $html .= '<p>Nu există consultații pentru acest pacient.</p>';
} else {
  $html .= '<table class="table">';
  $html .= '<tr>
    <th style="width:6%;">Nr</th>
    <th style="width:12%;">Data</th>
    <th style="width:16%;">Specialitate</th>
    <th style="width:16%;">Doctor</th>
    <th style="width:25%;">Diagnostic</th>
    <th style="width:25%;">Medicație</th>
  </tr>';

  foreach ($consultatii as $c) {
    $doctor = trim(($c['d_prenume'] ?? '') . ' ' . ($c['d_nume'] ?? ''));
    $html .= '<tr>';
    $html .= '<td>' . $h((string)$c['nr_consultatie']) . '</td>';
    $html .= '<td>' . $h($c['data_consultatie']) . '</td>';
    $html .= '<td>' . $h($c['specialitate']) . '</td>';
    $html .= '<td>' . $h($doctor) . '</td>';
    $html .= '<td>' . $h($c['diagnostic']) . '</td>';
    $html .= '<td>' . $h($c['medicamentatie']) . '</td>';
    $html .= '</tr>';
  }

  $html .= '</table>';
}

$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output('fisa_pacient_' . $cnp . '.pdf', 'I');
