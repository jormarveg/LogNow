<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/biblioteca_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$estadoFiltro = $_GET['estado'] ?? '';
$paginaBibliotecaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPaginaBiblioteca = 12;
$estadosValidos = ['jugando', 'completado', 'pendiente', 'abandonado'];

if (!in_array($estadoFiltro, $estadosValidos, true)) {
    $estadoFiltro = '';
}

$idUsuario = (int) getUsuario()['id'];
$resumenBiblioteca = cacheResumenBibliotecaUsuario($db, $idUsuario);
$totalJuegosBiblioteca = cacheContarBibliotecaUsuario($db, $idUsuario, $estadoFiltro);
$totalPaginasBiblioteca = max(1, (int) ceil($totalJuegosBiblioteca / $porPaginaBiblioteca));

if ($paginaBibliotecaActual > $totalPaginasBiblioteca) {
    $paginaBibliotecaActual = $totalPaginasBiblioteca;
}

$offsetBiblioteca = ($paginaBibliotecaActual - 1) * $porPaginaBiblioteca;
$juegosBiblioteca = cacheListarBibliotecaUsuario($db, $idUsuario, $estadoFiltro, $porPaginaBiblioteca, $offsetBiblioteca);
$contadorFiltros = [
    '' => $resumenBiblioteca['total'],
    'jugando' => $resumenBiblioteca['jugando'],
    'completado' => $resumenBiblioteca['completados'],
    'pendiente' => $resumenBiblioteca['pendientes'],
    'abandonado' => $resumenBiblioteca['abandonados']
];
$filtros = [
    '' => 'Todos',
    'jugando' => 'Jugando',
    'completado' => 'Completados',
    'pendiente' => 'Pendientes',
    'abandonado' => 'Abandonados'
];
$baseBibliotecaUrl = '/mis-juegos.php';

$titulo = 'Mis juegos — LogNow!';
$css = ['biblioteca.css'];
$pagina = 'mis-juegos';
require '../includes/header.php';
?>

<main class="container">
    <?php require '../includes/bloque-mis-juegos.php'; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
