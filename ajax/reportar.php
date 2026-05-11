<?php
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

$idResena = (int) ($_POST['id_resena'] ?? 0);
$motivo = trim((string) ($_POST['motivo'] ?? ''));
$longitudMotivo = mb_strlen($motivo, 'UTF-8');

if ($idResena <= 0 || $longitudMotivo < 5 || $longitudMotivo > 255) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Escribe un motivo entre 5 y 255 caracteres'
    ]);
    exit;
}

$stmtResena = $db->prepare('SELECT id, id_usuario, activa, comentario
                            FROM RESENA
                            WHERE id = ?
                            LIMIT 1');
$stmtResena->execute([$idResena]);
$resena = $stmtResena->fetch();

if (!$resena || !(int) $resena['activa'] || trim((string) $resena['comentario']) === '') {
    http_response_code(404);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'La reseña ya no está disponible'
    ]);
    exit;
}

$idUsuario = (int) getUsuario()['id'];

if ((int) $resena['id_usuario'] === $idUsuario) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No puedes reportar tu propia reseña'
    ]);
    exit;
}

$stmtDuplicado = $db->prepare('SELECT id
                               FROM REPORTE
                               WHERE id_usuario = ? AND id_resena = ?
                               LIMIT 1');
$stmtDuplicado->execute([$idUsuario, $idResena]);

if ($stmtDuplicado->fetch()) {
    http_response_code(409);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Ya has reportado esta reseña'
    ]);
    exit;
}

$stmtInsert = $db->prepare('INSERT INTO REPORTE (id_usuario, id_resena, motivo)
                            VALUES (?, ?, ?)');
$stmtInsert->execute([$idUsuario, $idResena, $motivo]);

echo json_encode([
    'ok' => true,
    'mensaje' => 'Reporte enviado correctamente.'
]);
