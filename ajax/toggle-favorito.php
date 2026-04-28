<?php
require '../api/cache.php';
require '../includes/auth.php';

header('Content-Type: application/json; charset=UTF-8');

if (!estaLogueado()) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes iniciar sesion'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

$idVideojuego = (int) ($_POST['id_videojuego'] ?? 0);
$favorito = isset($_POST['favorito']) ? (int) $_POST['favorito'] : null;

if ($idVideojuego <= 0 || ($favorito !== 0 && $favorito !== 1)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Datos no validos'
    ]);
    exit;
}

$actualizado = cacheActualizarFavoritoJuegoBiblioteca($db, (int) getUsuario()['id'], $idVideojuego, $favorito === 1);

if (!$actualizado) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => $favorito === 1 ? 'Has alcanzado el límite de juegos favoritos' : 'No se ha podido actualizar el favorito'
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'favorito' => $favorito === 1
]);
