<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

require_login();
$u = auth_user();

$diagnostic = trim((string)($_GET['diagnostic'] ?? ''));

layout_header('Rapoarte', $u);
?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h5 mb-2">Fișa unei afecțiuni (PDF)</h2>
        <p class="text-muted small mb-3">
          Raportul afișează lista pacienților care au avut consultații la doctorul logat și au diagnosticul căutat.
        </p>

        <form method="get" action="/clinica/public/raport_afectiune_pdf.php" target="_blank">
          <div class="mb-2">
            <label class="form-label">Diagnostic / Afecțiune</label>
            <input
              name="diagnostic"
              class="form-control"
              placeholder="ex: diabet / hipertensiune / astm / cancer"
              required
              value="<?= e($diagnostic) ?>"
            >
            <div class="form-text">Caută după text (LIKE). Exemplu: “diabet”.</div>
          </div>

          <button class="btn btn-primary">Generează PDF</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 mb-2">Sugestii rapide</h2>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_afectiune_pdf.php?diagnostic=diabet">Diabet</a>

          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_afectiune_pdf.php?diagnostic=hipertensiune">Hipertensiune</a>

          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_afectiune_pdf.php?diagnostic=astm">Astm</a>

          <a class="btn btn-outline-secondary btn-sm" target="_blank"
             href="/clinica/public/raport_afectiune_pdf.php?diagnostic=cancer">Cancer</a>
        </div>

        <hr>

        
      </div>
    </div>
  </div>
</div>

<?php
layout_footer();
