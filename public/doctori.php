<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

require_login();
$u = auth_user();

// doar directorul vede pagina
if ((int)($u['is_director'] ?? 0) !== 1) {
  http_response_code(403);
  exit('Acces interzis.');
}

$q = trim((string)($_GET['q'] ?? ''));
$params = [];

$sql = "
  SELECT
    d.id,
    d.cnp_doctor,
    d.nume,
    d.prenume,
    d.email,
    d.is_director,
    s.nume AS specialitate
  FROM doctori d
  JOIN specialitati s ON s.id = d.specialitate_id
";

if ($q !== '') {
  $sql .= " WHERE d.nume LIKE :q OR d.prenume LIKE :q OR d.email LIKE :q OR d.cnp_doctor LIKE :q OR s.nume LIKE :q";
  $params[':q'] = "%$q%";
}

$sql .= " ORDER BY s.nume ASC, d.prenume ASC, d.nume ASC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$doctori = $stmt->fetchAll();

layout_header('Doctori', $u);
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h5 mb-3">Căutare doctor</h2>

        <form method="get" class="d-flex gap-2">
          <input
            name="q"
            value="<?= e($q) ?>"
            class="form-control"
            placeholder="nume / email / CNP / specialitate"
          >
          <button class="btn btn-outline-secondary">Caută</button>
        </form>

        <div class="text-muted small mt-2">
          Total rezultate: <b><?= (int)count($doctori) ?></b>
        </div>

        <?php if ($q !== ''): ?>
          <div class="mt-2">
            <a class="btn btn-sm btn-outline-secondary" href="/clinica/public/doctori.php">Reset</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h2 class="h5 mb-0">Listă doctori</h2>
        </div>

        <?php if (!$doctori): ?>
          <div class="text-muted">Nu există rezultate.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Nume</th>
                  <th>Email</th>
                  <th>CNP</th>
                  <th>Specialitate</th>
                  <th>Rol</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($doctori as $d): ?>
                <tr>
                  <td class="fw-semibold"><?= e($d['prenume'].' '.$d['nume']) ?></td>
                  <td><?= e($d['email']) ?></td>
                  <td><?= e($d['cnp_doctor']) ?></td>
                  <td><?= e($d['specialitate']) ?></td>
                  <td>
                    <?php if ((int)$d['is_director'] === 1): ?>
                      <span class="badge text-bg-primary">Director</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">Doctor</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php
layout_footer();
