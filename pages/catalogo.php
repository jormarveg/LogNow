<?php
require '../api/cache.php';

$titulo = 'Catálogo — LogNow!';
$css = ['catalogo.css'];
$pagina = 'catalogo';

$filtroGenero = isset($_GET['genero']) ? (int) $_GET['genero'] : 0;
$filtroPlataforma = isset($_GET['plataforma']) ? (int) $_GET['plataforma'] : 0;
$filtroAnio = isset($_GET['anio']) ? (int) $_GET['anio'] : 0;
$orden = $_GET['orden'] ?? 'puntuacion';
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPagina = 12;

$filtros = [
    'genero' => $filtroGenero,
    'plataforma' => $filtroPlataforma,
    'anio' => $filtroAnio
];

$generos = cacheOpcionesGeneros($db);
$plataformas = cacheOpcionesPlataformas($db);
$anios = cacheOpcionesAnos($db);
$totalJuegos = cacheContarJuegosCatalogo($db, $filtros);
$totalPaginas = max(1, (int) ceil($totalJuegos / $porPagina));

if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}

$offset = ($paginaActual - 1) * $porPagina;
$juegos = cacheListarJuegosCatalogo($db, $filtros, $orden, $porPagina, $offset);

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
            <button type="submit">Aplicar</button>
        </form>
    </section>
    <section class="juegos">
        <?php if ($juegos): ?>
            <?php foreach ($juegos as $juego): ?>
                <article class="juego">
                    <div class="portada">
                        <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                    </div>
                    <div class="titulo"><p><?= htmlspecialchars($juego['titulo']) ?></p></div>
                    <div class="puntuacion">
                        <i class="fa-solid fa-star"></i>
                        <span><?= $juego['rating_rawg'] !== null ? number_format((float) $juego['rating_rawg'], 1) : 'N/D' ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sin-resultados">
                <p>No hay juegos disponibles con esos filtros.</p>
                <p>Importa primero juegos desde RAWG para llenar el catálogo.</p>
            </div>
        <?php endif; ?>
    </section>
    <?php if ($totalPaginas > 1): ?>
        <nav class="paginacion">
            <?php if ($paginaActual > 1): ?>
                <a href="<?= htmlspecialchars(urlCatalogo(['p' => $paginaActual - 1])) ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="<?= htmlspecialchars(urlCatalogo(['p' => $i])) ?>"<?= $i === $paginaActual ? ' class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

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
