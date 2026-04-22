<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

auth_start();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim((string)($_POST['email'] ?? ''));
  $pass  = (string)($_POST['password'] ?? '');

  if (auth_login($email, $pass)) {
    redirect('/clinica/public/index.php');
  } else {
    $err = "Email sau parola greșite.";
  }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Autentificare | Clinica</title>

  <style>
    html, body {
      height: 100%;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }

    /* FUNDAL CU IMAGINE */
    .login-wrapper {
      min-height: 100vh;
      background:
        linear-gradient(
          to right,
          rgba(13,110,253,0.65) 0%,
          rgba(13,110,253,0.55) 35%,
          rgba(0,0,0,0.15) 55%,
          rgba(0,0,0,0.05) 100%
        ),
        url('/clinica/public/login-bg.jpg') center / cover no-repeat;
      display: flex;
      align-items: center;
    }

    /* CARD LOGIN */
    .login-card {
      width: 100%;
      max-width: 420px;
      margin-left: 6%;
      border: none;
      border-radius: 1rem;
      background: rgba(255,255,255,0.96);
      box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    }

    .login-icon {
      font-size: 3rem;
      color: #0d6efd;
    }

    /* MOBILE */
    @media (max-width: 768px) {
      .login-wrapper {
        justify-content: center;
        background:
          linear-gradient(
            rgba(13,110,253,0.85),
            rgba(13,110,253,0.85)
          ),
          url('/clinica/public/login-bg.jpg') center / cover no-repeat;
      }

      .login-card {
        margin: 0 1rem;
      }
    }
  </style>
</head>

<body>

<div class="login-wrapper">
  <div class="card login-card">
    <div class="card-body p-4 p-md-5">

      <div class="text-center mb-4">
        <div class="login-icon">🩺</div>
        <h1 class="h4 mt-2 mb-1">Clinica Medicală</h1>
        <div class="text-muted">Autentificare personal</div>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email"
                 type="email"
                 class="form-control form-control-lg"
                 placeholder="doctor@clinica.ro"
                 required>
        </div>

        <div class="mb-4">
          <label class="form-label">Parola</label>
          <input name="password"
                 type="password"
                 class="form-control form-control-lg"
                 placeholder="••••••••"
                 required>
        </div>

        <button class="btn btn-primary btn-lg w-100">
          Intră în aplicație
        </button>
      </form>

      <div class="text-center text-muted small mt-4">
        Director default:<br>
        <b>ion.popescu@clinica.ro</b> / <b>parola123</b>
      </div>

    </div>
  </div>
</div>

</body>
</html>
