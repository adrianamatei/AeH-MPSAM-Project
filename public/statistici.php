<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

require_login();
$u = auth_user();

if ((int)($u['is_director'] ?? 0) !== 1) {
  http_response_code(403);
  exit('Acces interzis: doar Director.');
}

// Total pacienți unici care apar în consultatii (pentru procente)
$tot = db()->query("SELECT COUNT(DISTINCT cnp_pacient) AS total FROM consultatii")->fetch();
$totalPatients = (int)($tot['total'] ?? 0);

// 1) Specialități
$specStmt = db()->prepare("
  SELECT s.nume AS label, COUNT(DISTINCT c.cnp_pacient) AS cnt
  FROM consultatii c
  JOIN specialitati s ON s.id = c.specialitate_id
  GROUP BY s.id, s.nume
  ORDER BY cnt DESC, s.nume ASC
");
$specStmt->execute();
$specRows = $specStmt->fetchAll();

$specLabels = array_map(fn($r) => (string)$r['label'], $specRows);
$specCounts = array_map(fn($r) => (int)$r['cnt'], $specRows);

// 2) Boli cronice (text match în diagnostic)
$chronic = [
  'Diabet' => 'diabet',
  'Hipertensiune' => 'hipertensiune',
  'Astm' => 'astm',
  'Cancer' => 'cancer',
];

$chLabels = [];
$chCounts = [];

foreach ($chronic as $label => $needle) {
  $st = db()->prepare("
    SELECT COUNT(DISTINCT cnp_pacient) AS cnt
    FROM consultatii
    WHERE LOWER(diagnostic) LIKE :q
  ");
  $st->execute([':q' => '%' . mb_strtolower($needle) . '%']);
  $row = $st->fetch();
  $chLabels[] = $label;
  $chCounts[] = (int)($row['cnt'] ?? 0);
}

layout_header('Statistici', $u);
?>

<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    Total pacienți unici cu cel puțin o consultație:
    <b><?= e((string)$totalPatients) ?></b>
  </div>

  <div class="text-muted small">
    Datele sunt calculate din tabela <b>consultatii</b>.
  </div>
</div>

<div class="row g-3">

  <!-- SPECIALITĂȚI -->
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
          <h2 class="h5 mb-0">Pacienți cu consultații pe specialități</h2>
          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_statistici_pdf.php?type=specialitati">
            PDF
          </a>
        </div>

        <?php if (!$specLabels): ?>
          <div class="text-muted">Nu există date pentru specialități (încă nu sunt consultații).</div>
        <?php else: ?>
          <div class="row g-3">
            <div class="col-lg-6">
              <div class="border rounded-3 p-2 bg-white">
                <div class="text-muted small mb-1">Distribuție (pie)</div>
                <canvas id="specPie" height="220"></canvas>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="border rounded-3 p-2 bg-white">
                <div class="text-muted small mb-1">Comparație (bar)</div>
                <canvas id="specBar" height="220"></canvas>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- CRONICE -->
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
          <h2 class="h5 mb-0">Pacienți cu boli cronice (Diabet / Hipertensiune / Astm / Cancer)</h2>
          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_statistici_pdf.php?type=cronice">
            PDF
          </a>
        </div>

        <div class="text-muted small">
          Notă: un pacient poate apărea în mai multe categorii (dacă are mai multe diagnostice).
        </div>

        <div class="row g-3 mt-1">
          <div class="col-lg-6">
            <div class="border rounded-3 p-2 bg-white">
              <div class="text-muted small mb-1">Distribuție (pie)</div>
              <canvas id="chrPie" height="220"></canvas>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="border rounded-3 p-2 bg-white">
              <div class="text-muted small mb-1">Comparație (bar)</div>
              <canvas id="chrBar" height="220"></canvas>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const totalPatients = <?= (int)$totalPatients ?>;

const specLabels = <?= json_encode($specLabels, JSON_UNESCAPED_UNICODE) ?>;
const specCounts = <?= json_encode($specCounts, JSON_UNESCAPED_UNICODE) ?>;

const chrLabels = <?= json_encode($chLabels, JSON_UNESCAPED_UNICODE) ?>;
const chrCounts = <?= json_encode($chCounts, JSON_UNESCAPED_UNICODE) ?>;

// culori simple generate (HSL)
function genColors(n) {
  const arr = [];
  for (let i = 0; i < n; i++) {
    const hue = Math.round((360 * i) / Math.max(1, n));
    arr.push(`hsl(${hue}, 70%, 55%)`);
  }
  return arr;
}

function makePie(ctx, labels, data) {
  return new Chart(ctx, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: genColors(labels.length)
      }]
    },
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            label: (tt) => {
              const v = tt.raw ?? 0;
              const p = totalPatients > 0 ? (v * 100 / totalPatients) : 0;
              return ` ${tt.label}: ${v} (${p.toFixed(1)}%)`;
            }
          }
        },
        legend: {
          position: 'bottom'
        }
      }
    }
  });
}

function makeBar(ctx, labels, data) {
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Pacienți (număr)',
        data,
        backgroundColor: genColors(labels.length)
      }]
    },
    options: {
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });
}

if (specLabels.length) {
  makePie(document.getElementById('specPie'), specLabels, specCounts);
  makeBar(document.getElementById('specBar'), specLabels, specCounts);
}

makePie(document.getElementById('chrPie'), chrLabels, chrCounts);
makeBar(document.getElementById('chrBar'), chrLabels, chrCounts);
</script>

<?php
layout_footer();
