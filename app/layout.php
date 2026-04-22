<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Folosire:
 *   require_once __DIR__.'/../app/layout.php';
 *   layout_header('Titlu', $u);
 *   ... conținutul paginii ...
 *   layout_footer();
 */

function layout_header(string $title, array $u): void {
  $isDirector = ((int)($u['is_director'] ?? 0) === 1);
  ?>
  <!doctype html>
  <html lang="ro">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title><?= e($title) ?></title>

    <style>
      :root{
        --bg: #f6f8fb;
        --brand1: #0d6efd;
        --brand2: #20c997;
      }
      body{
        background: var(--bg);
      }
      .topbar{
        background: linear-gradient(135deg, var(--brand1) 0%, var(--brand2) 100%);
      }
      .topbar .btn-outline-light{
        border-color: rgba(255,255,255,.55);
      }
      .card{
        border: none;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      }
      .table thead th{
        color: #4b5563;
        font-weight: 600;
      }
      .page-title{
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:12px;
      }
      .page-title h1{
        margin:0;
      }
      .badge-role{
        background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.25);
      }
    </style>
  </head>
  <body>

  <nav class="navbar navbar-expand-lg navbar-dark topbar">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="/clinica/public/index.php">Clinica MedPRO</a>

      <div class="ms-auto d-flex flex-wrap gap-2 align-items-center text-white">
        <a class="btn btn-sm btn-outline-light" href="/clinica/public/pacienti.php">Pacienți</a>
        <a class="btn btn-sm btn-outline-light" href="/clinica/public/consultatii.php">Consultații</a>
        <a class="btn btn-sm btn-outline-light" href="/clinica/public/rapoarte.php">Rapoarte</a>
        <?php if ($isDirector): ?>
          <a class="btn btn-sm btn-outline-light" href="/clinica/public/statistici.php">Statistici</a>
          <a class="btn btn-sm btn-outline-light" href="/clinica/public/doctori.php">Doctori</a>
        <?php endif; ?>

        <span class="small ms-1">
          <?= e(($u['prenume'] ?? '').' '.($u['nume'] ?? '')) ?>
          <span class="badge badge-role ms-1"><?= $isDirector ? 'Director' : 'Doctor' ?></span>
        </span>

        <a class="btn btn-sm btn-outline-light" href="/clinica/public/logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
  <?php
}

function layout_footer(): void {
  ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>
  <?php
}
