<?php
require_once __DIR__ . '/../app/config.php';

if (!isLoggedIn()) {
    redirect(url('login.php'));
}

// Redirect către dashboard-ul corespunzător rolului
$user = currentUser();
if ($user['rol'] === 'medic') {
    redirect(url('dashboard_medic.php'));
} elseif ($user['rol'] === 'pacient') {
    redirect(url('dashboard_pacient.php'));
} else {
    redirect(url('login.php'));
}
