<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/biblioteca_helpers.php';
require '../includes/perfil_helpers.php';
require '../includes/listas_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'perfil';
$tabsValidas = ['perfil', 'juegos', 'listas', 'resenas'];

if (!in_array($tab, $tabsValidas, true)) {
    header('Location: /perfil.php');
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

$datosUsuario = $usuarioModel->obtenerPorId(getUsuario()['id']);

if (!$datosUsuario) {
    cerrarSesion();
}

$idUsuario = (int) getUsuario()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'listas') {
    $accion = $_POST['accion'] ?? '';
    $idLista = (int) ($_POST['id_lista'] ?? 0);

    if ($accion === 'borrar' && $idLista > 0) {
        listaBorrar($db, $idUsuario, $idLista);
        header('Location: /perfil.php?tab=listas&lista=borrada');
        exit;
    }
}

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
$listasPerfil = listasUsuario($db, $idUsuario);
$mensajeLista = $_GET['lista'] ?? '';
$perfilPropio = true;
$urlPerfilBase = '/perfil.php';

$titulo = 'Perfil — LogNow!';
$css = ['resenas.css', 'perfil.css', 'biblioteca.css', 'listas.css'];
$js = ['resenas.js'];
$pagina = $tab === 'juegos' ? 'mis-juegos' : ($tab === 'listas' ? 'listas' : 'perfil');
require '../includes/header.php';
require '../includes/perfil-vista.php';
require '../includes/nav_inferior.php';
require '../includes/footer.php';
