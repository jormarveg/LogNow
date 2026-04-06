<?php

require_once __DIR__ . '/cache.php';

$esCli = PHP_SAPI === 'cli';

if (!$esCli) {
    require_once __DIR__ . '/../includes/auth.php';

    if (!esAdmin()) {
        http_response_code(403);
        echo 'Acceso no permitido';
        exit;
    }

    header('Content-Type: text/plain; charset=UTF-8');
}

$pagina = $esCli ? ($argv[1] ?? 1) : ($_GET['pagina'] ?? 1);
$cantidad = $esCli ? ($argv[2] ?? 20) : ($_GET['cantidad'] ?? 20);

$resultado = cacheImportarPopulares($db, (int) $pagina, (int) $cantidad);

echo $resultado['mensaje'] . PHP_EOL;
echo 'Juegos importados: ' . $resultado['importados'] . PHP_EOL;
