<?php
require '../api/cache.php';
require '../includes/busqueda_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'buscar_igdb') {
    $busquedaPost = trim((string) ($_POST['q'] ?? ''));
    $resultado = 'error';

    if (mb_strlen($busquedaPost, 'UTF-8') >= BUSQUEDA_MINIMA_JUEGOS) {
        $resultadoIgdb = cacheImportarBusquedaIgdb($db, $busquedaPost, 1, BUSQUEDA_POR_PAGINA);
        $resultado = !empty($resultadoIgdb['ok']) ? (((int) ($resultadoIgdb['importados'] ?? 0) > 0) ? '' : 'sin_resultados') : 'error';
    }

    $params = [];

    if ($busquedaPost !== '') {
        $params['q'] = $busquedaPost;
    }

    if ($resultado !== '') {
        $params['igdb'] = $resultado;
    }

    header('Location: /buscar.php?' . http_build_query($params));
    exit;
}

$busqueda = trim($_GET['q'] ?? '');
$busquedaHeader = $busqueda;
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$avisoBusqueda = '';
$claseAvisoBusqueda = '';
$datosBusqueda = datosBusquedaLocal($db, $busqueda, $paginaActual, BUSQUEDA_POR_PAGINA);
$paginaActual = $datosBusqueda['pagina_actual'];
$totalJuegos = $datosBusqueda['total_juegos'];
$totalPaginas = $datosBusqueda['total_paginas'];
$juegos = $datosBusqueda['juegos'];

if ($busqueda !== '' && $datosBusqueda['longitud'] < BUSQUEDA_MINIMA_JUEGOS) {
    $avisoBusqueda = 'Escribe al menos ' . BUSQUEDA_MINIMA_JUEGOS . ' caracteres para buscar.';
} elseif (($_GET['igdb'] ?? '') === 'sin_resultados') {
    $avisoBusqueda = 'IGDB no ha devuelto resultados para esa búsqueda.';
} elseif (($_GET['igdb'] ?? '') === 'error') {
    $avisoBusqueda = 'No se ha podido completar la búsqueda externa.';
    $claseAvisoBusqueda = ' error';
}

$titulo = $busqueda !== '' ? 'Buscar: ' . $busqueda . ' — LogNow!' : 'Buscar juegos — LogNow!';
$css = ['catalogo.css'];
$js = ['busqueda.js'];
$usarJquery = true;
$pagina = 'buscar';

require '../includes/header.php';
?>

<main class="container busqueda-page" data-busqueda-minima="<?= BUSQUEDA_MINIMA_JUEGOS ?>" data-busqueda-pagina="<?= $paginaActual ?>">
    <h1>Buscar juegos</h1>

    <section class="encabezado encabezado-busqueda">
        <p id="resumen-busqueda"><?= htmlspecialchars(textoResumenBusqueda($busqueda, $totalJuegos)) ?></p>
        <p class="estado-busqueda-ajax" id="estado-busqueda-ajax" aria-live="polite"></p>
    </section>

    <form method="GET" class="filtros-catalogo formulario-busqueda-movil">
        <label>
            <span><i class="fa-solid fa-magnifying-glass"></i> Nombre del juego</span>
            <input type="text" name="q" minlength="<?= BUSQUEDA_MINIMA_JUEGOS ?>" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ej: Hollow Knight">
        </label>
        <button type="submit">Buscar</button>
    </form>

    <div class="aviso-busqueda<?= $claseAvisoBusqueda ?>" id="aviso-busqueda"<?= $avisoBusqueda === '' ? ' hidden' : '' ?>>
        <p><?= htmlspecialchars($avisoBusqueda) ?></p>
    </div>

    <form method="POST" class="form-importar-igdb" id="form-importar-igdb"<?= ($busqueda === '' || $datosBusqueda['longitud'] < BUSQUEDA_MINIMA_JUEGOS) ? ' hidden' : '' ?>>
        <input type="hidden" name="accion" value="buscar_igdb">
        <input type="hidden" name="q" value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit">Buscar también en IGDB</button>
    </form>

    <section class="busqueda-contenido" id="busqueda-contenido">
        <section class="juegos" id="resultados-busqueda">
            <?= htmlResultadosBusqueda($juegos, $busqueda, $datosBusqueda['longitud']) ?>
        </section>

        <div id="paginacion-busqueda">
            <?= htmlPaginacionBusqueda($busqueda, $paginaActual, $totalPaginas) ?>
        </div>
    </section>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
