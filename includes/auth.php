<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Usuario.php';

$usuarioModel = new Usuario($db);

function estaLogueado() {
    return isset($_SESSION['usuario']);
}

function esAdmin() {
    return estaLogueado() && $_SESSION['usuario']['rol'] === 'admin';
}

function getUsuario() {
    return $_SESSION['usuario'] ?? null;
}

function iniciarSesion($usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'nick' => $usuario['nick'],
        'rol' => $usuario['rol']
    ];
}

function cerrarSesion() {
    $_SESSION = [];
    session_destroy();
    header('Location: /');
    exit;
}
