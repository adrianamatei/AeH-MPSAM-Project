<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

auth_logout();
redirect('/clinica/public/login.php');
