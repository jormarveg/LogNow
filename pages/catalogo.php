<?php
require '../api/cache.php';
require '../includes/paginacion_helpers.php';

$titulo = 'Catálogo — LogNow!';
$css = ['catalogo.css'];
$pagina = 'catalogo';

$filtroGenero = isset($_GET['genero']) ? (int) $_GET['genero'] : 0;
$filtroPlataforma = isset($_GET['plataforma']) ? (int) $_GET['plataforma'] : 0;
$filtroAnio = isset($_GET['anio']) ? (int) $_GET['anio'] : 0;
$orden = $_GET['orden'] ?? 'puntuacion';
$direccion = $_GET['direccion'] ?? 'normal';
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPagina = 18;

if (!in_array($orden, ['puntuacion', 'nombre', 'fecha'], true)) {
    $orden = 'puntuacion';
}

if (!in_array($direccion, ['normal', 'inversa'], true)) {
    $direccion = 'normal';
}

$filtros = [
    'genero' => $filtroGenero,
    'plataforma' => $filtroPlataforma,
    'anio' => $filtroAnio
];
$filtrosActivos = $filtroGenero > 0
    || $filtroPlataforma > 0
    || $filtroAnio > 0
    || $orden !== 'puntuacion'
    || $direccion !== 'normal';

$generos = cacheOpcionesGeneros($db);
$plataformas = cacheOpcionesPlataformas($db);
$anios = cacheOpcionesAnos($db);
$totalJuegos = cacheContarJuegosCatalogo($db, $filtros);
$totalPaginas = max(1, (int) ceil($totalJuegos / $porPagina));

if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}

$offset = ($paginaActual - 1) * $porPagina;
$juegos = cacheListarJuegosCatalogo($db, $filtros, $orden, $porPagina, $offset, $direccion);

function urlCatalogo($cambios = []) {
    $params = $_GET;

    foreach ($cambios as $clave => $valor) {
        if ($valor === null || $valor === '' || $valor === 0) {
            unset($params[$clave]);
        } else {
            $params[$clave] = $valor;
        }
    }

    $query = http_build_query($params);

    return '/catalogo.php' . ($query ? '?' . $query : '');
}

require '../includes/header.php';
?>

<main class="container">
    <h1>Todos los juegos</h1>
    <section class="encabezado">
        <p><?= number_format($totalJuegos, 0, ',', '.') ?> juegos</p>
        <div class="filtros-catalogo-wrapper">
            <input class="control-filtros-catalogo" type="checkbox" id="toggle-filtros-catalogo" autocomplete="off"<?= $filtrosActivos ? ' checked' : '' ?>>
            <label class="boton-filtros-catalogo" for="toggle-filtros-catalogo">
                <span><i class="fa-solid fa-filter"></i> Filtros</span>
                <i class="fa-solid fa-chevron-down"></i>
            </label>
            <form method="GET" class="filtros-catalogo">
                <label>
                    <span><i class="fa-solid fa-filter"></i> Género</span>
                    <select name="genero">
                        <option value="0">Todos</option>
                        <?php foreach ($generos as $genero): ?>
                            <option value="<?= $genero['id'] ?>"<?= $filtroGenero === (int) $genero['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($genero['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Plataforma</span>
                    <select name="plataforma">
                        <option value="0">Todas</option>
                        <?php foreach ($plataformas as $plataforma): ?>
                            <option value="<?= $plataforma['id'] ?>"<?= $filtroPlataforma === (int) $plataforma['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($plataforma['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Año</span>
                    <select name="anio">
                        <option value="0">Todos</option>
                        <?php foreach ($anios as $item): ?>
                            <option value="<?= $item['anio'] ?>"<?= $filtroAnio === (int) $item['anio'] ? ' selected' : '' ?>>
                                <?= $item['anio'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Ordenar por</span>
                    <select name="orden">
                        <option value="puntuacion"<?= $orden === 'puntuacion' ? ' selected' : '' ?>>Puntuación</option>
                        <option value="nombre"<?= $orden === 'nombre' ? ' selected' : '' ?>>Nombre</option>
                        <option value="fecha"<?= $orden === 'fecha' ? ' selected' : '' ?>>Fecha</option>
                    </select>
                </label>
                <label>
                    <span>Orden</span>
                    <select name="direccion">
                        <option value="normal"<?= $direccion === 'normal' ? ' selected' : '' ?>>Normal</option>
                        <option value="inversa"<?= $direccion === 'inversa' ? ' selected' : '' ?>>Inversa</option>
                    </select>
                </label>
                <button type="submit">Aplicar</button>
            </form>
        </div>
    </section>
    <section class="juegos">
        <?php if ($juegos): ?>
            <?php foreach ($juegos as $juego): ?>
                <a class="juego" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">
                    <div class="portada">
                        <img src="<?= htmlspecialchars(urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                        <div class="titulo">
                            <p><?= htmlspecialchars($juego['titulo']) ?></p>
                        </div>
                    </div>
                    <div class="puntuacion puntuacion-<?= htmlspecialchars($juego['origen_puntuacion']) ?>">
                        <i class="fa-solid fa-star"></i>
                        <span><?= $juego['puntuacion_visible'] !== null ? number_format((float) $juego['puntuacion_visible'], 1) : 'N/D' ?></span>
                        <?php if ($juego['origen_puntuacion'] === 'igdb'): ?>
                            <small>IGDB</small>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sin-resultados">
                <p>No hay juegos disponibles con esos filtros.</p>
                <p>Importa primero juegos desde IGDB para llenar el catálogo.</p>
            </div>
        <?php endif; ?>
    </section>
    <?php if ($totalPaginas > 1): ?>
        <nav class="paginacion">
            <?php if ($paginaActual > 1): ?>
                <a href="<?= htmlspecialchars(urlCatalogo(['p' => $paginaActual - 1])) ?>">Anterior</a>
            <?php endif; ?>

            <?php foreach (paginasCompactas($paginaActual, $totalPaginas) as $item): ?>
                <?php if ($item === '...'): ?>
                    <span class="separador">...</span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(urlCatalogo(['p' => $item])) ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <a href="<?= htmlspecialchars(urlCatalogo(['p' => $paginaActual + 1])) ?>">Siguiente</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
