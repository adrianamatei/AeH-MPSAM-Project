<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';

require_login();
$u = auth_user();

if ((int)($u['is_director'] ?? 0) !== 1) {
  http_response_code(403);
  exit('Acces interzis: doar Director.');
}

$type = trim((string)($_GET['type'] ?? ''));

if (!in_array($type, ['specialitati', 'cronice'], true)) {
  http_response_code(400);
  exit('Parametru type invalid. Folosește ?type=specialitati sau ?type=cronice');
}

$tot = db()->query("SELECT COUNT(DISTINCT cnp_pacient) AS total FROM consultatii")->fetch();
$totalPatients = (int)($tot['total'] ?? 0);

$rows = [];
$title = '';

if ($type === 'specialitati') {
  $title = 'Statistici: pacienți cu consultații pe specialități';
  $st = db()->prepare("
    SELECT s.nume AS label, COUNT(DISTINCT c.cnp_pacient) AS cnt
    FROM consultatii c
    JOIN specialitati s ON s.id = c.specialitate_id
    GROUP BY s.id, s.nume
    ORDER BY cnt DESC, s.nume ASC
  ");
  $st->execute();
  $rows = $st->fetchAll();
}

if ($type === 'cronice') {
  $title = 'Statistici: pacienți cu boli cronice (diabet/hipertensiune/astm/cancer)';

  $defs = [
    ['Diabet', 'diabet'],
    ['Hipertensiune', 'hipertensiune'],
    ['Astm', 'astm'],
    ['Cancer', 'cancer'],
  ];

  foreach ($defs as [$label, $needle]) {
    $st = db()->prepare("
      SELECT COUNT(DISTINCT cnp_pacient) AS cnt
      FROM consultatii
      WHERE LOWER(diagnostic) LIKE :q
    ");
    $st->execute([':q' => '%' . mb_strtolower($needle) . '%']);
    $r = $st->fetch();
    $rows[] = ['label' => $label, 'cnt' => (int)($r['cnt'] ?? 0)];
  }
}

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

$css = <<<CSS
body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #111; }
h1 { font-size: 15pt; margin: 0 0 6px 0; }
.small { font-size: 9.5pt; color: #555; margin-bottom: 10px; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { border: 1px solid #333; padding: 6px; vertical-align: top; }
.table th { background: #f2f2f2; }
.right { text-align: right; }
CSS;

$doctorName = trim(($u['prenume'] ?? '') . ' ' . ($u['nume'] ?? ''));

$html = '';
$html .= '<h1>' . $h($title) . '</h1>';
$html .= '<div class="small">'
      .  'Generat de: ' . $h($doctorName) . ' (Director)<br>'
      .  'Total pacienți unici cu cel puțin o consultație: <b>' . $h((string)$totalPatients) . '</b><br>'
      .  'Generat la: ' . date('d.m.Y H:i')
      .  '</div>';

if ($type === 'cronice') {
  $html .= '<div class="small">Notă: un pacient poate apărea în mai multe categorii.</div>';
}

$html .= '<table class="table">';
$html .= '<tr><th>Categorie</th><th class="right">Număr pacienți</th><th class="right">Procent</th></tr>';

foreach ($rows as $r) {
  $cnt = (int)$r['cnt'];
  $pct = $totalPatients > 0 ? ($cnt * 100 / $totalPatients) : 0;
  $html .= '<tr>';
  $html .= '<td>' . $h($r['label']) . '</td>';
  $html .= '<td class="right">' . $h((string)$cnt) . '</td>';
  $html .= '<td class="right">' . $h(number_format($pct, 1)) . '%</td>';
  $html .= '</tr>';
}
$html .= '</table>';

$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$fname = $type === 'specialitati' ? 'statistici_specialitati.pdf' : 'statistici_cronice.pdf';
$mpdf->Output($fname, 'I');
