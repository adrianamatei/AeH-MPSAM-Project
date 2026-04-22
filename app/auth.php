<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function auth_start(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
}

function auth_user(): ?array {
  auth_start();
  return $_SESSION['user'] ?? null;
}

function auth_login(string $email, string $password): bool {
  auth_start();
  $stmt = db()->prepare("SELECT id, nume, prenume, email, parola_hash, is_director, specialitate_id 
                        FROM doctori WHERE email = :email");
  $stmt->execute([':email' => $email]);
  $u = $stmt->fetch();

  if (!$u) return false;
  if (!password_verify($password, $u['parola_hash'])) return false;

  unset($u['parola_hash']);
  $_SESSION['user'] = $u;
  return true;
}

function auth_logout(): void {
  auth_start();
  $_SESSION = [];
  session_destroy();
}
