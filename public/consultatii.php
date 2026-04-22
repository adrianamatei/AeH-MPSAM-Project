<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

require_login();
$u = auth_user();

$flash = '';
$doctorId = (int)$u['id'];
$doctorSpecId = (int)$u['specialitate_id'];

// PRECOMPLETARE PACIENT DIN LINK (?cnp=...)
$prefillCnp = trim((string)($_GET['cnp'] ?? ''));
$prefillPacient = null;

if ($prefillCnp !== '') {
  $ps = db()->prepare("SELECT cnp, nume, prenume FROM pacienti WHERE cnp = :cnp");
  $ps->execute([':cnp' => $prefillCnp]);
  $prefillPacient = $ps->fetch() ?: null;

  if (!$prefillPacient) {
    $flash = "Pacientul selectat nu există. Adaugă-l mai întâi la «Pacienți».";
    $prefillCnp = '';
  }
}

// DELETE (doar consultațiile doctorului logat)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'delete') {
    $idDel = (int)($_POST['id'] ?? 0);
    if ($idDel > 0) {
      $del = db()->prepare("DELETE FROM consultatii WHERE id = :id AND doctor_id = :did");
      $del->execute([':id' => $idDel, ':did' => $doctorId]);
      $flash = "Consultația a fost ștearsă (dacă îți aparținea).";
    }
  }
}

// EDIT LOAD
$editId = (int)($_GET['edit'] ?? 0);
$editConsult = null;

if ($editId > 0) {
  $es = db()->prepare("
    SELECT c.*
    FROM consultatii c
    WHERE c.id = :id AND c.doctor_id = :did
  ");
  $es->execute([':id' => $editId, ':did' => $doctorId]);
  $editConsult = $es->fetch() ?: null;

  if (!$editConsult) {
    $flash = "Consultația pentru editare nu a fost găsită (sau nu îți aparține).";
    $editId = 0;
  } else {
    // precompletează pacientul (consistență UI)
    $prefillCnp = (string)$editConsult['cnp_pacient'];
    $ps = db()->prepare("SELECT cnp, nume, prenume FROM pacienti WHERE cnp = :cnp");
    $ps->execute([':cnp' => $prefillCnp]);
    $prefillPacient = $ps->fetch() ?: null;
  }
}

$isEdit = (bool)$editConsult;

// UI: următorul nr (doar când adaugi și ai pacient preselectat)
$nextNr = '';
if (!$isEdit && $prefillCnp !== '') {
  $ms = db()->prepare("SELECT COALESCE(MAX(nr_consultatie), 0) AS m FROM consultatii WHERE cnp_pacient = :cnp");
  $ms->execute([':cnp' => $prefillCnp]);
  $nextNr = (string)(((int)($ms->fetch()['m'] ?? 0)) + 1);
}

// SAVE (INSERT/UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);

    $cnp = trim((string)($_POST['cnp_pacient'] ?? ''));
    $data = (string)($_POST['data_consultatie'] ?? '');
    $diagnostic = trim((string)($_POST['diagnostic'] ?? ''));
    $med = trim((string)($_POST['medicamentatie'] ?? ''));

    if (!preg_match('/^\d{13}$/', $cnp)) {
      $flash = "CNP pacient invalid (13 cifre).";
    } elseif ($data === '') {
      $flash = "Data consultației este obligatorie.";
    } elseif ($diagnostic === '') {
      $flash = "Diagnosticul este obligatoriu.";
    } elseif ($med === '') {
      $flash = "Medicația este obligatorie.";
    } else {
      // verifică pacient existent
      $ps = db()->prepare("SELECT cnp FROM pacienti WHERE cnp = :cnp");
      $ps->execute([':cnp' => $cnp]);
      if (!$ps->fetch()) {
        $flash = "Pacientul cu acest CNP nu există. Adaugă pacientul înainte.";
      } else {
        try {
          if ($id > 0) {
            // UPDATE (nr_consultatie rămâne neschimbat)
            $up = db()->prepare("
              UPDATE consultatii
              SET cnp_pacient = :cnp,
                  data_consultatie = :data,
                  diagnostic = :diag,
                  medicamentatie = :med,
                  specialitate_id = :sid
              WHERE id = :id AND doctor_id = :did
            ");
            $up->execute([
              ':cnp' => $cnp,
              ':data' => $data,
              ':diag' => $diagnostic,
              ':med' => $med,
              ':sid' => $doctorSpecId,
              ':id' => $id,
              ':did' => $doctorId
            ]);

            redirect('/clinica/public/consultatii.php');
          } else {
            // INSERT: nr_consultatie automat = MAX+1 per pacient
            $ms = db()->prepare("SELECT COALESCE(MAX(nr_consultatie), 0) AS m FROM consultatii WHERE cnp_pacient = :cnp");
            $ms->execute([':cnp' => $cnp]);
            $nr = (int)($ms->fetch()['m'] ?? 0) + 1;

            $ins = db()->prepare("
              INSERT INTO consultatii (cnp_pacient, nr_consultatie, data_consultatie, diagnostic, medicamentatie, doctor_id, specialitate_id)
              VALUES (:cnp, :nr, :data, :diag, :med, :did, :sid)
            ");
            $ins->execute([
              ':cnp' => $cnp,
              ':nr' => $nr,
              ':data' => $data,
              ':diag' => $diagnostic,
              ':med' => $med,
              ':did' => $doctorId,
              ':sid' => $doctorSpecId
            ]);

            redirect('/clinica/public/consultatii.php?cnp=' . urlencode($cnp));
          }
        } catch (PDOException $e) {
          $flash = "Eroare la salvare: a apărut o problemă (posibil dublură nr. consultație).";
        }
      }
    }
  }
}

// FILTERS (listă)
$cnpFilter = trim((string)($_GET['cnp'] ?? ''));
$diagFilter = trim((string)($_GET['diag'] ?? ''));
$dateFrom = trim((string)($_GET['from'] ?? ''));
$dateTo = trim((string)($_GET['to'] ?? ''));

$where = ["c.doctor_id = :did"];
$params = [':did' => $doctorId];

if ($cnpFilter !== '') {
  $where[] = "c.cnp_pacient LIKE :cnp";
  $params[':cnp'] = "%$cnpFilter%";
}
if ($diagFilter !== '') {
  $where[] = "LOWER(c.diagnostic) LIKE :diag";
  $params[':diag'] = "%" . mb_strtolower($diagFilter) . "%";
}
if ($dateFrom !== '') {
  $where[] = "c.data_consultatie >= :df";
  $params[':df'] = $dateFrom;
}
if ($dateTo !== '') {
  $where[] = "c.data_consultatie <= :dt";
  $params[':dt'] = $dateTo;
}

$sql = "
  SELECT
    c.*,
    p.nume AS p_nume,
    p.prenume AS p_prenume,
    s.nume AS specialitate
  FROM consultatii c
  JOIN pacienti p ON p.cnp = c.cnp_pacient
  JOIN specialitati s ON s.id = c.specialitate_id
  WHERE " . implode(" AND ", $where) . "
  ORDER BY c.data_consultatie DESC, c.id DESC
  LIMIT 300
";

$list = db()->prepare($sql);
$list->execute($params);
$consultatii = $list->fetchAll();

function val(?array $row, string $key): string {
  return e((string)($row[$key] ?? ''));
}

layout_header('Consultații', $u);
?>

<?php if ($flash): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 mb-2">Filtre</h2>
        <form method="get" class="vstack gap-2">
          <input name="cnp" value="<?= e($cnpFilter) ?>" class="form-control" placeholder="CNP pacient (opțional)">
          <input name="diag" value="<?= e($diagFilter) ?>" class="form-control" placeholder="Diagnostic (opțional)">

          <div class="d-flex gap-2">
            <div class="w-50">
              <label class="form-label small mb-1">De la</label>
              <input type="date" name="from" value="<?= e($dateFrom) ?>" class="form-control">
            </div>
            <div class="w-50">
              <label class="form-label small mb-1">Până la</label>
              <input type="date" name="to" value="<?= e($dateTo) ?>" class="form-control">
            </div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary w-50">Aplică</button>
            <a class="btn btn-outline-secondary w-50" href="/clinica/public/consultatii.php">Reset</a>
          </div>
        </form>

        <div class="form-text mt-2">
          Se afișează doar consultațiile doctorului logat.
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h5 mb-3"><?= $isEdit ? 'Editează consultație' : 'Adaugă consultație' ?></h2>

        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)($editConsult['id'] ?? 0) ?>">

          <div class="mb-2">
            <label class="form-label">CNP pacient</label>
            <input name="cnp_pacient"
                   class="form-control"
                   required
                   value="<?= $isEdit ? val($editConsult, 'cnp_pacient') : e($prefillCnp) ?>"
                   <?= ($prefillCnp !== '' || $isEdit) ? 'readonly' : '' ?>>

            <?php if (($prefillCnp !== '' || $isEdit) && $prefillPacient): ?>
              <div class="form-text">
                Pacient: <b><?= e($prefillPacient['prenume'].' '.$prefillPacient['nume']) ?></b>
              </div>
            <?php else: ?>
              <div class="form-text">Pacientul trebuie să existe deja în “Pacienți”.</div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label class="form-label">Nr. consultație</label>
            <?php if ($isEdit): ?>
              <input type="number" class="form-control" value="<?= val($editConsult, 'nr_consultatie') ?>" readonly>
              <div class="form-text">Numărul nu se modifică (se generează automat la creare).</div>
            <?php else: ?>
              <input type="number" class="form-control" value="<?= e($nextNr) ?>" readonly>
              <div class="form-text">Se generează automat la salvare.</div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label class="form-label">Data consultației</label>
            <input type="date" name="data_consultatie" class="form-control" required
                   value="<?= val($editConsult, 'data_consultatie') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Diagnostic</label>
            <input name="diagnostic" class="form-control" required
                   value="<?= val($editConsult, 'diagnostic') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Medicație</label>
            <textarea name="medicamentatie" class="form-control" rows="4" required><?= val($editConsult, 'medicamentatie') ?></textarea>
          </div>

          <button class="btn btn-primary w-100"><?= $isEdit ? 'Actualizează' : 'Salvează' ?></button>

          <?php if ($isEdit): ?>
            <a class="btn btn-outline-secondary w-100 mt-2" href="/clinica/public/consultatii.php">Anulează</a>
          <?php endif; ?>

          <?php if ($prefillCnp !== '' && !$isEdit): ?>
            <a class="btn btn-outline-secondary w-100 mt-2"
               href="/clinica/public/pacienti.php?edit=<?= urlencode($prefillCnp) ?>">
              Înapoi la pacient
            </a>
          <?php endif; ?>
        </form>
      </div>
    </div>

  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h2 class="h5 mb-0">Listă consultații</h2>
          <div class="text-muted small">max. 300 rezultate</div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Pacient</th>
                <th>CNP</th>
                <th>Nr</th>
                <th>Data</th>
                <th>Specialitate</th>
                <th>Diagnostic</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($consultatii as $c): ?>
              <tr>
                <td><?= e($c['p_prenume'].' '.$c['p_nume']) ?></td>
                <td><?= e($c['cnp_pacient']) ?></td>
                <td>
                  <a class="text-decoration-none"
                     href="/clinica/public/consultatii.php?edit=<?= (int)$c['id'] ?>">
                    <?= e((string)$c['nr_consultatie']) ?>
                  </a>
                </td>
                <td><?= e($c['data_consultatie']) ?></td>
                <td><?= e($c['specialitate']) ?></td>
                <td><?= e($c['diagnostic']) ?></td>
                <td class="d-flex gap-2">
                  <form method="post" onsubmit="return confirm('Sigur ștergi consultația?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Șterge</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
layout_footer();
