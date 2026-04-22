<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

function require_login(): void {
  if (!auth_user()) redirect('/clinica/public/login.php');
}

function require_director(): void {
  $u = auth_user();
  if (!$u || (int)$u['is_director'] !== 1) {
    http_response_code(403);
    echo "Acces interzis (doar director).";
    exit;
  }
}
