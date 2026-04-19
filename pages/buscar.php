<?php
require '../api/cache.php';

$busqueda = trim($_GET['q'] ?? '');
$busquedaHeader = $busqueda;
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPagina = 18;
$mensajeBusqueda = '';
$usoIgdb = false;

if ($busqueda !== '') {
    $totalJuegos = cacheContarBusquedaLocal($db, $busqueda);

    if ($totalJuegos === 0 && $paginaActual === 1) {
        $resultadoIgdb = cacheImportarBusquedaIgdb($db, $busqueda, 1, 12);

        if ($resultadoIgdb['ok']) {
            $usoIgdb = true;
            $totalJuegos = cacheContarBusquedaLocal($db, $busqueda);

            if ($totalJuegos > 0) {
                $mensajeBusqueda = 'No había resultados y se han cargado juegos nuevos.';
            } else {
                $mensajeBusqueda = 'No se ha encontrado ningún juego con ese nombre.';
            }
        } elseif ($resultadoIgdb['mensaje'] !== 'No hay credenciales de IGDB configuradas') {
            $mensajeBusqueda = 'No se ha podido completar la búsqueda en este momento.';
        }
    }
} else {
    $totalJuegos = 0;
}

$totalPaginas = max(1, (int) ceil($totalJuegos / $porPagina));

if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}

$offset = ($paginaActual - 1) * $porPagina;
$juegos = $busqueda !== '' ? cacheBuscarJuegosLocal($db, $busqueda, $porPagina, $offset) : [];

function urlBusqueda($cambios = []) {
    $params = $_GET;

    foreach ($cambios as $clave => $valor) {
        if ($valor === null || $valor === '') {
            unset($params[$clave]);
        } else {
            $params[$clave] = $valor;
        }
    }

    $query = http_build_query($params);

    return '/buscar.php' . ($query ? '?' . $query : '');
}

function paginasBusqueda($paginaActual, $totalPaginas) {
    if ($totalPaginas <= 7) {
        return range(1, $totalPaginas);
    }

    $paginas = [1];
    $inicio = max(2, $paginaActual - 1);
    $fin = min($totalPaginas - 1, $paginaActual + 1);

    if ($inicio > 2) {
        $paginas[] = '...';
    }

    for ($i = $inicio; $i <= $fin; $i++) {
        $paginas[] = $i;
    }

    if ($fin < $totalPaginas - 1) {
        $paginas[] = '...';
    }

    $paginas[] = $totalPaginas;

    return $paginas;
}

$titulo = $busqueda !== '' ? 'Buscar: ' . $busqueda . ' — LogNow!' : 'Buscar juegos — LogNow!';
$css = ['catalogo.css'];
$pagina = 'buscar';

require '../includes/header.php';
?>

<main class="container">
    <h1>Buscar juegos</h1>

    <section class="encabezado encabezado-busqueda">
        <?php if ($busqueda !== ''): ?>
            <p><?= number_format($totalJuegos, 0, ',', '.') ?> resultados para "<?= htmlspecialchars($busqueda) ?>"</p>
        <?php else: ?>
            <p>Busca cualquier juego por su nombre.</p>
        <?php endif; ?>
    </section>

    <form method="GET" class="filtros-catalogo formulario-busqueda-movil">
        <label>
            <span><i class="fa-solid fa-magnifying-glass"></i> Nombre del juego</span>
            <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ej: Hollow Knight">
        </label>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($mensajeBusqueda): ?>
        <div class="aviso-busqueda<?= $usoIgdb ? ' aviso-igdb' : '' ?>">
            <p><?= htmlspecialchars($mensajeBusqueda) ?></p>
        </div>
    <?php endif; ?>

    <section class="juegos">
        <?php if ($juegos): ?>
            <?php foreach ($juegos as $juego): ?>
                <a class="juego" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">
                    <div class="portada">
                        <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                        <div class="titulo">
                            <p><?= htmlspecialchars($juego['titulo']) ?></p>
                        </div>
                    </div>
                    <div class="puntuacion">
                        <i class="fa-solid fa-star"></i>
                        <span><?= $juego['puntuacion_media'] !== null ? number_format((float) $juego['puntuacion_media'], 1) : 'N/D' ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php elseif ($busqueda !== ''): ?>
            <div class="sin-resultados">
                <p>No se ha encontrado ningún juego con ese nombre.</p>
                <p>Prueba con otro título o con una palabra más corta.</p>
            </div>
        <?php else: ?>
            <div class="sin-resultados">
                <p>Escribe el nombre de un juego para empezar.</p>
                <p>También puedes probar con una palabra clave.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($totalPaginas > 1): ?>
        <nav class="paginacion">
            <?php if ($paginaActual > 1): ?>
                <a href="<?= htmlspecialchars(urlBusqueda(['p' => $paginaActual - 1])) ?>">Anterior</a>
            <?php endif; ?>

            <?php foreach (paginasBusqueda($paginaActual, $totalPaginas) as $item): ?>
                <?php if ($item === '...'): ?>
                    <span class="separador">...</span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(urlBusqueda(['p' => $item])) ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <a href="<?= htmlspecialchars(urlBusqueda(['p' => $paginaActual + 1])) ?>">Siguiente</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
