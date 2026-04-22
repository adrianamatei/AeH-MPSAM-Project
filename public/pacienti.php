<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/guards.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/layout.php';

require_login();
$u = auth_user();

$flash = '';

// DELETE (cu ștergere consultații + pacient)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'delete') {
    $cnpDel = trim((string)($_POST['cnp'] ?? ''));

    if ($cnpDel !== '') {
      db()->beginTransaction();
      try {
        $d1 = db()->prepare("DELETE FROM consultatii WHERE cnp_pacient = :cnp");
        $d1->execute([':cnp' => $cnpDel]);

        $d2 = db()->prepare("DELETE FROM pacienti WHERE cnp = :cnp");
        $d2->execute([':cnp' => $cnpDel]);

        db()->commit();
        $flash = "Șters.";
      } catch (Throwable $e) {
        db()->rollBack();
        $flash = "Eroare la ștergere.";
      }
    }
  }
}

// EDIT LOAD (precomplete form)
$editCnp = trim((string)($_GET['edit'] ?? ''));
$editPacient = null;

if ($editCnp !== '') {
  $es = db()->prepare("SELECT * FROM pacienti WHERE cnp = :cnp");
  $es->execute([':cnp' => $editCnp]);
  $editPacient = $es->fetch() ?: null;

  if (!$editPacient) {
    $flash = "Pacientul pentru editare nu a fost găsit.";
    $editCnp = '';
  }
}

// CREATE/UPDATE (UPSERT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'save') {
    $nume = trim((string)($_POST['nume'] ?? ''));
    $prenume = trim((string)($_POST['prenume'] ?? ''));
    $cnp = trim((string)($_POST['cnp'] ?? ''));
    $data_n = (string)($_POST['data_nasterii'] ?? '');
    $sex = (string)($_POST['sex'] ?? '');
    $varsta = (int)($_POST['varsta'] ?? 0);
    $adresa = trim((string)($_POST['adresa'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $telefon = trim((string)($_POST['telefon'] ?? ''));

    if ($cnp === '' || strlen($cnp) !== 13) {
      $flash = "CNP invalid (13 cifre).";
    } elseif (!in_array($sex, ['M', 'F'], true)) {
      $flash = "Sex invalid.";
    } else {
      $stmt = db()->prepare("
        INSERT INTO pacienti (cnp, nume, prenume, adresa, data_nasterii, sex, varsta, email, telefon)
        VALUES (:cnp, :nume, :prenume, :adresa, :dn, :sex, :v, :email, :tel)
        ON DUPLICATE KEY UPDATE
          nume = VALUES(nume),
          prenume = VALUES(prenume),
          adresa = VALUES(adresa),
          data_nasterii = VALUES(data_nasterii),
          sex = VALUES(sex),
          varsta = VALUES(varsta),
          email = VALUES(email),
          telefon = VALUES(telefon)
      ");
      $stmt->execute([
        ':cnp' => $cnp,
        ':nume' => $nume,
        ':prenume' => $prenume,
        ':adresa' => $adresa,
        ':dn' => $data_n,
        ':sex' => $sex,
        ':v' => $varsta,
        ':email' => $email,
        ':tel' => $telefon
      ]);

      redirect('/clinica/public/pacienti.php');
    }
  }
}

// SEARCH + LIST
$q = trim((string)($_GET['q'] ?? ''));
$mine = (int)($_GET['mine'] ?? 0);

$params = [];

if ($mine === 1) {
  $sql = "
    SELECT DISTINCT p.*
    FROM pacienti p
    JOIN consultatii c ON c.cnp_pacient = p.cnp
    WHERE c.doctor_id = :did
  ";
  $params[':did'] = (int)$u['id'];

  if ($q !== '') {
    $sql .= " AND (p.cnp LIKE :q OR p.email LIKE :q OR p.telefon LIKE :q OR p.nume LIKE :q OR p.prenume LIKE :q)";
    $params[':q'] = "%$q%";
  }
  $sql .= " ORDER BY p.created_at DESC LIMIT 200";

} else {
  $sql = "SELECT * FROM pacienti";

  if ($q !== '') {
    $sql .= " WHERE cnp LIKE :q OR email LIKE :q OR telefon LIKE :q OR nume LIKE :q OR prenume LIKE :q";
    $params[':q'] = "%$q%";
  }

  $sql .= " ORDER BY created_at DESC LIMIT 200";
}

$list = db()->prepare($sql);
$list->execute($params);
$pacienti = $list->fetchAll();

function v(?array $row, string $key): string {
  return e((string)($row[$key] ?? ''));
}
$isEdit = (bool)$editPacient;

layout_header('Pacienți', $u);
?>

<style>
  .table-responsive { overflow-x: auto !important; overflow-y: visible !important; }
  .card, .card-body { overflow: visible !important; }
  td.actions-cell { position: static !important; }
</style>

<?php if ($flash): ?>
  <div class="alert alert-info"><?= e($flash) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 mb-2">Căutare</h2>
        <form method="get" class="d-flex gap-2">
          <?php if ((int)($_GET['mine'] ?? 0) === 1): ?>
            <input type="hidden" name="mine" value="1">
          <?php endif; ?>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="CNP / nume / email / telefon">
          <button class="btn btn-outline-secondary">Caută</button>
        </form>

        <div class="d-flex gap-2 mt-2">
          <a class="btn btn-sm <?= $mine===0 ? 'btn-secondary' : 'btn-outline-secondary' ?>"
             href="/clinica/public/pacienti.php<?= $q!=='' ? '?q='.urlencode($q) : '' ?>">
            Toți
          </a>

          <a class="btn btn-sm <?= $mine===1 ? 'btn-secondary' : 'btn-outline-secondary' ?>"
             href="/clinica/public/pacienti.php?mine=1<?= $q!=='' ? '&q='.urlencode($q) : '' ?>">
            Pacienții mei
          </a>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h5 mb-3"><?= $isEdit ? 'Editează pacient' : 'Adaugă pacient' ?></h2>

        <form method="post">
          <input type="hidden" name="action" value="save">

          <div class="mb-2">
            <label class="form-label">Nume</label>
            <input name="nume" id="nume" class="form-control" required value="<?= v($editPacient, 'nume') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Prenume</label>
            <input name="prenume" id="prenume" class="form-control" required value="<?= v($editPacient, 'prenume') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">CNP</label>
            <input name="cnp" id="cnp" class="form-control" maxlength="13" required
                   value="<?= v($editPacient, 'cnp') ?>"
                   <?= $isEdit ? 'readonly' : '' ?>>
            <?php if ($isEdit): ?>
              <div class="form-text">CNP nu poate fi modificat în modul edit.</div>
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label class="form-label">Sex</label>
            <?php $sexVal = (string)($editPacient['sex'] ?? ''); ?>
            <select name="sex" id="sex" class="form-select" required>
              <option value="">-- alege --</option>
              <option value="M" <?= $sexVal === 'M' ? 'selected' : '' ?>>Masculin</option>
              <option value="F" <?= $sexVal === 'F' ? 'selected' : '' ?>>Feminin</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Data nașterii</label>
            <input type="date" name="data_nasterii" id="data_nasterii" class="form-control" required
                   value="<?= v($editPacient, 'data_nasterii') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Vârstă</label>
            <input type="number" name="varsta" id="varsta" class="form-control" min="0" max="130" required readonly
                   value="<?= v($editPacient, 'varsta') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Adresă</label>
            <input name="adresa" class="form-control" required value="<?= v($editPacient, 'adresa') ?>">
          </div>

          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= v($editPacient, 'email') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Telefon</label>
            <input name="telefon" class="form-control" required value="<?= v($editPacient, 'telefon') ?>">
          </div>

          <button class="btn btn-primary w-100"><?= $isEdit ? 'Actualizează' : 'Salvează' ?></button>

          <?php if ($isEdit): ?>
            <a class="btn btn-outline-secondary w-100 mt-2" href="/clinica/public/pacienti.php">Anulează</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h2 class="h5 mb-0">Listă pacienți</h2>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Nume</th>
                <th>CNP</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Vârstă</th>
                <th>Acțiuni</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($pacienti as $p): ?>
              <tr>
                <td>
                  <a href="/clinica/public/pacienti.php?edit=<?= urlencode($p['cnp']) ?>" class="text-decoration-none">
                    <?= e($p['prenume'].' '.$p['nume']) ?>
                  </a>
                </td>
                <td><?= e($p['cnp']) ?></td>
                <td><?= e($p['email']) ?></td>
                <td><?= e($p['telefon']) ?></td>
                <td><?= e((string)$p['varsta']) ?></td>
                <td class="text-nowrap actions-cell">
                  <button class="btn btn-sm btn-outline-secondary"
                          type="button"
                          data-bs-toggle="modal"
                          data-bs-target="#actModal<?= e($p['cnp']) ?>">
                    Acțiuni
                  </button>

                  <div class="modal fade" id="actModal<?= e($p['cnp']) ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">

                        <div class="modal-header">
                          <h5 class="modal-title">Acțiuni pacient</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                        </div>

                        <div class="modal-body">
                          <div class="mb-2">
                            <div class="fw-semibold"><?= e($p['prenume'].' '.$p['nume']) ?></div>
                            <div class="text-muted small">CNP: <?= e($p['cnp']) ?></div>
                          </div>

                          <div class="list-group">
                            <a class="list-group-item list-group-item-action"
                               href="/clinica/public/raport_pacient_pdf.php?cnp=<?= urlencode($p['cnp']) ?>">
                              📄 PDF fișă pacient
                            </a>

                            <a class="list-group-item list-group-item-action"
                               href="/clinica/public/consultatii.php?cnp=<?= urlencode($p['cnp']) ?>">
                              🩺 Consultații
                            </a>

                            <a class="list-group-item list-group-item-action"
                               href="/clinica/public/consultatii.php?cnp=<?= urlencode($p['cnp']) ?>">
                              ➕ Adaugă consultație
                            </a>

                            <button type="button"
                                    class="list-group-item list-group-item-action text-danger"
                                    onclick="confirmDelete('<?= e($p['cnp']) ?>')">
                              🗑️ Șterge pacient
                            </button>
                          </div>
                        </div>

                        <div class="modal-footer">
                          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Închide</button>
                        </div>

                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="text-muted small">Se afișează max. 200 rezultate.</div>
      </div>
    </div>
  </div>
</div>

<script>
function calcAge(birthDateStr) {
  const d = new Date(birthDateStr + "T00:00:00");
  if (isNaN(d.getTime())) return "";
  const today = new Date();
  let age = today.getFullYear() - d.getFullYear();
  const m = today.getMonth() - d.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
  return age < 0 ? "" : age;
}

function parseCNP(cnp) {
  if (!/^\d{13}$/.test(cnp)) return null;

  const s = parseInt(cnp[0], 10);
  const yy = parseInt(cnp.slice(1,3), 10);
  const mm = parseInt(cnp.slice(3,5), 10);
  const dd = parseInt(cnp.slice(5,7), 10);

  let century;
  if (s === 1 || s === 2) century = 1900;
  else if (s === 3 || s === 4) century = 1800;
  else if (s === 5 || s === 6) century = 2000;
  else if (s === 7 || s === 8) century = 1900;
  else if (s === 9) century = 1900;
  else return null;

  const year = century + yy;
  const sex = (s % 2 === 1) ? "M" : "F";

  const iso = `${year.toString().padStart(4,'0')}-${String(mm).padStart(2,'0')}-${String(dd).padStart(2,'0')}`;
  const test = new Date(iso + "T00:00:00");
  if (isNaN(test.getTime())) return null;
  if (test.getFullYear() !== year || (test.getMonth()+1) !== mm || test.getDate() !== dd) return null;

  return { birthdate: iso, sex };
}

const cnpInput = document.getElementById('cnp');
const birthInput = document.getElementById('data_nasterii');
const ageInput = document.getElementById('varsta');
const sexSelect = document.getElementById('sex');

const isEditMode = cnpInput?.hasAttribute('readonly');

function autofillFromCNP() {
  const cnp = (cnpInput.value || "").trim();
  const info = parseCNP(cnp);
  if (!info) return;

  if (birthInput) birthInput.value = info.birthdate;
  if (sexSelect) sexSelect.value = info.sex;
  if (ageInput) ageInput.value = calcAge(info.birthdate);
}

cnpInput?.addEventListener('input', () => {
  if (isEditMode) return;
  if (cnpInput.value.trim().length === 13) autofillFromCNP();
});
cnpInput?.addEventListener('blur', () => {
  if (isEditMode) return;
  autofillFromCNP();
});

birthInput?.addEventListener('change', () => {
  if (ageInput) ageInput.value = calcAge(birthInput.value);
});
</script>

<form id="deleteForm" method="post" style="display:none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="cnp" id="deleteCnp" value="">
</form>

<script>
function confirmDelete(cnp) {
  if (!confirm('Sigur ștergi pacientul și toate consultațiile lui?')) return;
  document.getElementById('deleteCnp').value = cnp;
  document.getElementById('deleteForm').submit();
}
</script>

<?php
layout_footer();
