<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

$u = auth_user();
if (!$u) redirect('/clinica/public/login.php');

$isDirector = ((int)($u['is_director'] ?? 0) === 1);

layout_header('Dashboard', $u);
?>

<div class="row g-3">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h1 class="h4 mb-1">Dashboard</h1>
          <div class="text-muted">
            Bine ai venit, <?= e(($u['prenume'] ?? '').' '.($u['nume'] ?? '')) ?>. Alege un modul de mai jos.
          </div>
        </div>
        <div class="text-muted small">
          Rol: <b><?= $isDirector ? 'Director' : 'Doctor' ?></b>
        </div>
      </div>
    </div>
  </div>

  <!-- Pacienți -->
  <div class="col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <h2 class="h5 mb-2">Pacienți</h2>
        <p class="text-muted small mb-3">
          Adaugă, editează, șterge și exportă fișa pacientului în PDF.
        </p>
        <div class="mt-auto">
          <a class="btn btn-primary w-100" href="/clinica/public/pacienti.php">Deschide</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Consultații -->
  <div class="col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <h2 class="h5 mb-2">Consultații</h2>
        <p class="text-muted small mb-3">
          Gestionează consultațiile: adaugă, modifică, șterge și filtrează.
        </p>
        <div class="mt-auto">
          <a class="btn btn-primary w-100" href="/clinica/public/consultatii.php">Deschide</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Rapoarte -->
  <div class="col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <h2 class="h5 mb-2">Rapoarte</h2>
        <p class="text-muted small mb-3">
          Generează fișa unei afecțiuni (PDF) pentru doctorul curent.
        </p>
        <div class="mt-auto">
          <a class="btn btn-primary w-100" href="/clinica/public/rapoarte.php">Deschide</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistici (doar director) -->
  <div class="col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <h2 class="h5 mb-2">Statistici</h2>
        <p class="text-muted small mb-3">
          Grafice + PDF: specialități și boli cronice.
        </p>

        <div class="mt-auto">
          <?php if ($isDirector): ?>
            <a class="btn btn-primary w-100" href="/clinica/public/statistici.php">Deschide</a>
          <?php else: ?>
            <button class="btn btn-outline-secondary w-100" disabled>Doar Director</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if ($isDirector): ?>
    <!-- Doctori (doar director) -->
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex flex-column">
          <h2 class="h5 mb-2">Doctori</h2>
          <p class="text-muted small mb-3">
            Vezi lista doctorilor și specializarea fiecăruia.
          </p>
          <div class="mt-auto">
            <a class="btn btn-primary w-100" href="/clinica/public/doctori.php">Deschide</a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php
layout_footer();
