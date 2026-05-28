<?php
require_once __DIR__ . '/../app/config.php';

logout();
flash('success', 'Te-ai deconectat cu succes.');
redirect(url('login.php'));
