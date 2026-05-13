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
$estado = trim((string) ($_POST['estado'] ?? ''));
$estadosValidos = ['completado', 'jugando', 'pendiente', 'abandonado'];

if ($idVideojuego <= 0 || !in_array($estado, $estadosValidos, true)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Datos no validos'
    ]);
    exit;
}

$resultado = cacheGuardarEstadoRapidoBiblioteca($db, (int) getUsuario()['id'], $idVideojuego, $estado);

if ($resultado === 'error') {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se ha podido actualizar el estado'
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'estado' => $estado,
    'creado' => $resultado === 'creado'
]);
