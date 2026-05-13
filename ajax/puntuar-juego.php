<?php
require '../api/cache.php';
require '../includes/auth.php';

header('Content-Type: application/json; charset=UTF-8');

if (!estaLogueado()) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes iniciar sesión'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

$idVideojuego = (int) ($_POST['id_videojuego'] ?? 0);
$puntuacion = trim((string) ($_POST['puntuacion'] ?? ''));

if ($idVideojuego <= 0 || ($puntuacion !== '' && (!ctype_digit($puntuacion) || !cachePuntuacionResenaValida((int) $puntuacion)))) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Datos no válidos'
    ]);
    exit;
}

$idUsuario = (int) getUsuario()['id'];
$creado = false;

try {
    $db->beginTransaction();

    if ($puntuacion !== '' && !cacheUsuarioJuego($db, $idVideojuego, $idUsuario)) {
        $resultadoEstado = cacheGuardarEstadoRapidoBiblioteca($db, $idUsuario, $idVideojuego, 'completado');

        if ($resultadoEstado === 'error') {
            throw new RuntimeException('biblioteca');
        }

        $creado = $resultadoEstado === 'creado';
    }

    if ($puntuacion !== '' && !cacheGuardarPuntuacionUsuario($db, $idUsuario, $idVideojuego, (int) $puntuacion)) {
        throw new RuntimeException('puntuacion');
    }

    if ($puntuacion === '' && !cacheLimpiarPuntuacionUsuario($db, $idUsuario, $idVideojuego)) {
        throw new RuntimeException('puntuacion');
    }

    $db->commit();

    echo json_encode([
        'ok' => true,
        'puntuacion' => $puntuacion,
        'puntuacion_visible' => $puntuacion === '' ? '' : number_format(((int) $puntuacion) / 20, ((int) $puntuacion % 20 === 0 ? 0 : 1), ',', '.'),
        'creado' => $creado
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se ha podido guardar la puntuación'
    ]);
}
