<?php
require __DIR__ . '/includes/app.php';

session_unset();
session_destroy();
redirect('login.php');
