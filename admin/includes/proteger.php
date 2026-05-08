<?php
require_once __DIR__ . '/../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

if (!esAdmin()) {
    header('Location: /');
    exit;
}
