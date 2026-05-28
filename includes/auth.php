<?php

//  30 días
$duracionSesion = 60 * 60 * 24 * 30;

ini_set('session.gc_maxlifetime', (string) $duracionSesion);
session_set_cookie_params([
    'lifetime' => $duracionSesion,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

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

function actualizarSesionUsuario($usuario) {
    if (!isset($_SESSION['usuario'])) {
        return;
    }

    $_SESSION['usuario']['nick'] = $usuario['nick'];
    $_SESSION['usuario']['rol'] = $usuario['rol'];
}

function comprobarSesionActiva($usuarioModel) {
    if (!estaLogueado()) {
        return;
    }

    $usuario = $usuarioModel->obtenerPorId($_SESSION['usuario']['id']);

    if (!$usuario || !(int) $usuario['activo']) {
        cerrarSesion('/login.php?cuenta=desactivada');
    }

    actualizarSesionUsuario($usuario);
}

function cerrarSesion($destino = '/?logout=ok') {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $destino);
    exit;
}

comprobarSesionActiva($usuarioModel);
