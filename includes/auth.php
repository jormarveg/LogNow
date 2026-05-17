<?php
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

function urlAvatarUsuario($avatar) {
    $avatar = trim((string) $avatar);

    return $avatar !== '' ? $avatar : '/assets/img/profile/user.webp';
}

function urlEncabezadoUsuario($encabezado) {
    $encabezado = trim((string) $encabezado);

    return $encabezado !== '' ? $encabezado : '/assets/img/profile/banner.webp';
}

function urlPortadaJuego($portada, $texto = 'Sin portada') {
    $portada = trim((string) $portada);

    if ($portada !== '') {
        return $portada;
    }

    $texto = trim((string) $texto);
    $texto = $texto !== '' ? $texto : 'Sin portada';
    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 450">'
        . '<rect width="300" height="450" rx="18" fill="#d1d5db"/>'
        . '<text x="150" y="225" text-anchor="middle" font-family="Poppins, Arial, sans-serif" font-size="24" font-weight="600" fill="#6a7785">' . $texto . '</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function cerrarSesion() {
    $_SESSION = [];
    session_destroy();
    header('Location: /?logout=ok');
    exit;
}
