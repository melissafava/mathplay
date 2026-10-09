<?php
require __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    redirect('inicio.php');
}

redirect('login.php');
