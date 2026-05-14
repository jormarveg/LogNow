<?php
require '../api/cache.php';
require '../includes/perfil_helpers.php';
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
$tipoBusqueda = tipoBusquedaValido($_GET['tipo'] ?? 'juegos');
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$avisoBusqueda = '';
$claseAvisoBusqueda = '';

if ($tipoBusqueda === 'usuarios') {
    $datosBusqueda = datosBusquedaUsuarios($db, $busqueda, $paginaActual, BUSQUEDA_USUARIOS_POR_PAGINA);
    $paginaActual = $datosBusqueda['pagina_actual'];
    $totalResultados = $datosBusqueda['total_usuarios'];
    $totalPaginas = $datosBusqueda['total_paginas'];
    $usuarios = $datosBusqueda['usuarios'];
    $juegos = [];
} else {
    $datosBusqueda = datosBusquedaLocal($db, $busqueda, $paginaActual, BUSQUEDA_POR_PAGINA);
    $paginaActual = $datosBusqueda['pagina_actual'];
    $totalResultados = $datosBusqueda['total_juegos'];
    $totalPaginas = $datosBusqueda['total_paginas'];
    $juegos = $datosBusqueda['juegos'];
    $usuarios = [];
}

if ($busqueda !== '' && $datosBusqueda['longitud'] < BUSQUEDA_MINIMA_JUEGOS) {
    $avisoBusqueda = 'Escribe al menos ' . BUSQUEDA_MINIMA_JUEGOS . ' caracteres para buscar.';
} elseif (($_GET['igdb'] ?? '') === 'sin_resultados') {
    $avisoBusqueda = 'IGDB no ha devuelto resultados para esa búsqueda.';
} elseif (($_GET['igdb'] ?? '') === 'error') {
    $avisoBusqueda = 'No se ha podido completar la búsqueda externa.';
    $claseAvisoBusqueda = ' error';
}

$titulo = $busqueda !== '' ? 'Buscar: ' . $busqueda . ' — LogNow!' : 'Buscar — LogNow!';
$css = ['catalogo.css'];
$pagina = 'buscar';

require '../includes/header.php';
?>

<main class="container">
    <h1>Buscar</h1>

    <section class="encabezado encabezado-busqueda">
        <p><?= htmlspecialchars(textoResumenBusqueda($busqueda, $totalResultados, $tipoBusqueda)) ?></p>
    </section>

    <form method="GET" class="filtros-catalogo formulario-busqueda-movil">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoBusqueda) ?>">
        <label>
            <span><i class="fa-solid fa-magnifying-glass"></i> Juego o usuario</span>
            <input type="text" name="q" minlength="<?= BUSQUEDA_MINIMA_JUEGOS ?>" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Ej: Hollow Knight">
        </label>
        <button type="submit">Buscar</button>
    </form>

    <?= htmlTabsBusqueda($busqueda, $tipoBusqueda) ?>

    <div class="aviso-busqueda<?= $claseAvisoBusqueda ?>"<?= $avisoBusqueda === '' ? ' hidden' : '' ?>>
        <p><?= htmlspecialchars($avisoBusqueda) ?></p>
    </div>

    <?php if ($tipoBusqueda === 'juegos'): ?>
        <form method="POST" class="form-importar-igdb"<?= ($busqueda === '' || $datosBusqueda['longitud'] < BUSQUEDA_MINIMA_JUEGOS) ? ' hidden' : '' ?>>
            <input type="hidden" name="accion" value="buscar_igdb">
            <input type="hidden" name="q" value="<?= htmlspecialchars($busqueda) ?>">
            <button type="submit">Buscar también en IGDB</button>
        </form>
    <?php endif; ?>

    <section>
        <?php if ($tipoBusqueda === 'usuarios'): ?>
            <section class="usuarios-busqueda">
                <?= htmlResultadosUsuariosBusqueda($usuarios, $busqueda, $datosBusqueda['longitud']) ?>
            </section>
        <?php else: ?>
            <section class="juegos">
                <?= htmlResultadosBusqueda($juegos, $busqueda, $datosBusqueda['longitud']) ?>
            </section>
        <?php endif; ?>

        <div>
            <?= htmlPaginacionBusqueda($busqueda, $paginaActual, $totalPaginas, $tipoBusqueda) ?>
        </div>
    </section>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
