<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/biblioteca_helpers.php';
require '../includes/perfil_helpers.php';

$nick = trim((string) ($_GET['nick'] ?? ''));
$datosUsuario = preg_match('/^[a-zA-Z0-9_]{3,20}$/', $nick) ? $usuarioModel->obtenerPorNick($nick) : null;

if ($datosUsuario && estaLogueado() && (int) $datosUsuario['id'] === (int) getUsuario()['id']) {
    header('Location: /perfil.php');
    exit;
}

if (!$datosUsuario) {
    http_response_code(404);

    $titulo = 'Usuario no encontrado — LogNow!';
    $css = ['perfil.css', 'biblioteca.css'];
    $pagina = 'usuario';
    require '../includes/header.php';
    ?>
    <main class="container">
        <div class="panel-vacio perfil-no-encontrado">
            <h1>Usuario no encontrado</h1>
            <p>No existe ningún perfil público con ese nick.</p>
            <a class="boton-secundario" href="/">Volver al inicio</a>
        </div>
    </main>
    <?php
    require '../includes/nav_inferior.php';
    require '../includes/footer.php';
    exit;
}

if ((int) $datosUsuario['activo'] !== 1) {
    $titulo = 'Perfil desactivado — LogNow!';
    $css = ['perfil.css', 'biblioteca.css'];
    $pagina = 'usuario';
    require '../includes/header.php';
    ?>
    <main class="container">
        <div class="panel-vacio perfil-no-encontrado">
            <h1>Perfil desactivado</h1>
            <p>Este perfil ha sido desactivado.</p>
            <a class="boton-secundario" href="/">Volver al inicio</a>
        </div>
    </main>
    <?php
    require '../includes/nav_inferior.php';
    require '../includes/footer.php';
    exit;
}

$tab = $_GET['tab'] ?? 'perfil';
$tabsValidas = ['perfil', 'juegos', 'resenas'];

if (!in_array($tab, $tabsValidas, true)) {
    header('Location: ' . urlUsuarioPublico($datosUsuario['nick']));
    exit;
}

$estadoFiltro = $_GET['estado'] ?? '';
$paginaBibliotecaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$paginaResenasActual = isset($_GET['rp']) ? max(1, (int) $_GET['rp']) : 1;
$porPaginaBiblioteca = 12;
$porPaginaResenas = 6;
if ($estadoFiltro !== '' && !estadoBibliotecaValido($estadoFiltro)) {
    $estadoFiltro = '';
}

$idUsuario = (int) $datosUsuario['id'];
$datosPerfil = datosPerfilUsuario($db, $idUsuario, $estadoFiltro, $paginaBibliotecaActual, $porPaginaBiblioteca, $paginaResenasActual, $porPaginaResenas);
$resumenBiblioteca = $datosPerfil['resumenBiblioteca'];
$totalJuegosBiblioteca = $datosPerfil['totalJuegosBiblioteca'];
$totalPaginasBiblioteca = $datosPerfil['totalPaginasBiblioteca'];
$paginaBibliotecaActual = $datosPerfil['paginaBibliotecaActual'];
$juegosBiblioteca = $datosPerfil['juegosBiblioteca'];
$resenasUsuarioPerfil = $datosPerfil['resenasUsuarioPerfil'];
$resenasUsuarioTab = $datosPerfil['resenasUsuarioTab'];
$totalResenasUsuario = $datosPerfil['totalResenasUsuario'];
$totalPaginasResenas = $datosPerfil['totalPaginasResenas'];
$paginaResenasActual = $datosPerfil['paginaResenasActual'];
$favoritosUsuario = $datosPerfil['favoritosUsuario'];
$jugadosEsteAno = $datosPerfil['jugadosEsteAno'];
$histogramaUsuario = $datosPerfil['histogramaUsuario'];
$totalPuntuacionesUsuario = $datosPerfil['totalPuntuacionesUsuario'];
$maximoHistograma = $datosPerfil['maximoHistograma'];
$contadorFiltros = $datosPerfil['contadorFiltros'];
$filtros = $datosPerfil['filtros'];
$perfilPropio = false;
$urlPerfilBase = urlUsuarioPublico($datosUsuario['nick']);

$titulo = htmlspecialchars($datosUsuario['nombre']) . ' — LogNow!';
$css = ['resenas.css', 'perfil.css', 'biblioteca.css'];
$js = ['resenas.js'];
$pagina = 'usuario';
require '../includes/header.php';
require '../includes/perfil-vista.php';
require '../includes/nav_inferior.php';
require '../includes/footer.php';
