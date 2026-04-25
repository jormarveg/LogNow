<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/biblioteca_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

function urlPerfilTab($tab, $extra = []) {
    $params = array_merge(['tab' => $tab], $extra);

    foreach ($params as $clave => $valor) {
        if ($valor === '' || $valor === null) {
            unset($params[$clave]);
        }
    }

    $query = http_build_query($params);

    return '/perfil.php' . ($query ? '?' . $query : '');
}

function fechaPerfilBonita($fecha, $abreviada = false) {
    if (!$fecha) {
        return '';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = $abreviada
        ? ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
        : ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    return date('j', $marca) . ' ' . $meses[(int) date('n', $marca) - 1] . ' ' . date('Y', $marca);
}

function puntuacionPerfilVisible($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function estrellasPerfil($puntuacion) {
    if ($puntuacion === null) {
        $puntuacion = 0;
    }

    $puntuacion = max(0, min(5, (float) $puntuacion));
    $estrellasCompletas = (int) floor($puntuacion);
    $mediaEstrella = ($puntuacion - $estrellasCompletas) >= 0.5;
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $estrellasCompletas) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($mediaEstrella && $i === $estrellasCompletas + 1) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-solid fa-star vacia"></i>';
        }
    }

    return $html;
}

function partesTextoPerfilResena($texto, $limite = 110) {
    $texto = trim((string) $texto);

    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return [$texto, ''];
    }

    $corto = mb_substr($texto, 0, $limite, 'UTF-8');
    $resto = mb_substr($texto, $limite, null, 'UTF-8');
    $ultimoEspacio = mb_strrpos($corto, ' ', 0, 'UTF-8');

    if ($ultimoEspacio !== false && $ultimoEspacio > 60) {
        $resto = mb_substr($corto, $ultimoEspacio + 1, null, 'UTF-8') . $resto;
        $corto = mb_substr($corto, 0, $ultimoEspacio, 'UTF-8');
    }

    return [rtrim($corto) . '...', ltrim($resto)];
}

$tab = $_GET['tab'] ?? 'perfil';
$tabsValidas = ['perfil', 'juegos', 'resenas'];

if (!in_array($tab, $tabsValidas, true)) {
    header('Location: /perfil.php');
    exit;
}

$estadoFiltro = $_GET['estado'] ?? '';
$estadosValidos = ['jugando', 'completado', 'pendiente', 'abandonado'];

if (!in_array($estadoFiltro, $estadosValidos, true)) {
    $estadoFiltro = '';
}

$datosUsuario = $usuarioModel->obtenerPorId(getUsuario()['id']);
$idUsuario = (int) getUsuario()['id'];
$resumenBiblioteca = cacheResumenBibliotecaUsuario($db, (int) getUsuario()['id']);
$juegosBiblioteca = cacheListarBibliotecaUsuario($db, (int) getUsuario()['id'], $estadoFiltro);
$resenasUsuarioPerfil = cacheListarResenasUsuario($db, $idUsuario, 5);
$resenasUsuarioTab = cacheListarResenasUsuario($db, $idUsuario, 20);
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
$baseBibliotecaUrl = '/perfil.php?tab=juegos';

$titulo = 'Perfil — LogNow!';
$css = ['resenas.css', 'perfil.css', 'biblioteca.css'];
$pagina = $tab === 'juegos' ? 'mis-juegos' : 'perfil';
require '../includes/header.php';
?>

<section class="encabezado-perfil">
    <div class="container">
        <div class="foto-perfil">
            <img src="/assets/img/profile/user.webp" alt="Foto de perfil">
        </div>
        <h1 class="nombre"><?= htmlspecialchars($datosUsuario['nombre']) ?></h1>
    </div>
</section>

<nav class="profile-tabs">
    <div class="container">
        <ul>
            <li<?= $tab === 'perfil' ? ' class="active"' : '' ?>><a href="/perfil.php"><i class="fa-solid fa-user"></i>Perfil</a></li>
            <li<?= $tab === 'juegos' ? ' class="active"' : '' ?>><a href="/perfil.php?tab=juegos"><i class="fa-solid fa-gamepad"></i>Tus juegos</a></li>
            <li<?= $tab === 'resenas' ? ' class="active"' : '' ?>><a href="/perfil.php?tab=resenas"><i class="fa-solid fa-message"></i>Tus reseñas</a></li>
            <li><a class="editar" href="#">Editar perfil</a></li>
        </ul>
    </div>
</nav>

<main class="container">
    <div class="content-grid">
        <aside class="sidebar">
            <section class="bio">
                <h2>Bio</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusamus alias consectetur cum
                    doloribus nesciunt, nobis officia pariatur porro totam.</p>
            </section>

            <section class="stats">
                <div class="jugados">
                    <h3>Jugados</h3>
                    <span class="datos">82</span>
                </div>
                <div class="este-ano">
                    <h3>Este año</h3>
                    <span class="datos">4</span>
                </div>
            </section>

            <section class="puntuaciones">
                <h3>Tus puntuaciones</h3>
                <div class="grafica">
                    <div class="barra barra1"></div>
                    <div class="barra barra2"></div>
                    <div class="barra barra3"></div>
                    <div class="barra barra4"></div>
                    <div class="barra barra5"></div>
                    <span>1 <i class="fa-solid fa-star"></i></span>
                    <span>2 <i class="fa-solid fa-star"></i></span>
                    <span>3 <i class="fa-solid fa-star"></i></span>
                    <span>4 <i class="fa-solid fa-star"></i></span>
                    <span>5 <i class="fa-solid fa-star"></i></span>
                </div>
            </section>
        </aside>
        <hr class="separador">
        <section class="principal">
            <?php if ($tab === 'perfil'): ?>
                <section class="favoritos">
                    <h2>Tus favoritos</h2>
                    <div class="carousel">
                        <div class="favorito">
                            <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <p>Juego 1</p>
                        </div>
                        <div class="favorito elemento-carousel">
                            <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <p>Juego 2</p>
                        </div>
                        <div class="favorito elemento-carousel">
                            <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <p>Juego 3</p>
                        </div>
                        <div class="favorito elemento-carousel">
                            <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <p>Juego 4</p>
                        </div>
                    </div>
                </section>

                <section class="resenas-recientes">
                    <h2>Tus reseñas recientes</h2>
                    <?php if ($resenasUsuarioPerfil): ?>
                        <div class="carousel">
                            <?php foreach ($resenasUsuarioPerfil as $resena): ?>
                                <?php [$textoCorto, $textoCompleto] = partesTextoPerfilResena($resena['comentario']); ?>
                                <div class="elemento-carousel mini-resena">
                                    <div class="mini-portada"><img src="<?= htmlspecialchars($resena['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
                                    <div class="nombre-puntuacion">
                                        <h4><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="puntuacion"><i class="fa-solid fa-star"></i><span><?= puntuacionPerfilVisible($resena['puntuacion_estrellas']) ?></span></div>
                                    </div>
                                    <div class="puntuacion-tablet">
                                        <div class="titulo-puntuacion-wrapper">
                                            <p class="titulo-plataforma">
                                                <strong><?= htmlspecialchars($resena['titulo']) ?></strong>
                                                <?php if (!empty($resena['plataforma'])): ?>
                                                    en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                                <?php endif; ?>
                                            </p>
                                            <div class="estrellas"><?= estrellasPerfil($resena['puntuacion_estrellas']) ?></div>
                                        </div>
                                        <p class="fecha"><?= fechaPerfilBonita($resena['fecha_publicacion']) ?></p>
                                    </div>
                                    <p class="texto"><?= htmlspecialchars($textoCorto) ?><?php if ($textoCompleto !== ''): ?><span class="completo"><?= htmlspecialchars($textoCompleto) ?></span><?php endif; ?></p>
                                    <p class="username"><?= htmlspecialchars($datosUsuario['nick']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2>Todavía no has publicado reseñas</h2>
                            <p>Cuando completes tu primera reseña desde un juego guardado en biblioteca, aparecerá aquí.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php elseif ($tab === 'juegos'): ?>
                <?php require '../includes/bloque-mis-juegos.php'; ?>
            <?php else: ?>
                <section class="resenas-recientes">
                    <h2>Tus reseñas recientes</h2>
                    <?php if ($resenasUsuarioTab): ?>
                        <div class="carousel">
                            <?php foreach ($resenasUsuarioTab as $resena): ?>
                                <?php [$textoCorto, $textoCompleto] = partesTextoPerfilResena($resena['comentario']); ?>
                                <div class="elemento-carousel mini-resena">
                                    <div class="mini-portada"><img src="<?= htmlspecialchars($resena['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
                                    <div class="nombre-puntuacion">
                                        <h4><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="puntuacion"><i class="fa-solid fa-star"></i><span><?= puntuacionPerfilVisible($resena['puntuacion_estrellas']) ?></span></div>
                                    </div>
                                    <div class="puntuacion-tablet">
                                        <div class="titulo-puntuacion-wrapper">
                                            <p class="titulo-plataforma">
                                                <strong><?= htmlspecialchars($resena['titulo']) ?></strong>
                                                <?php if (!empty($resena['plataforma'])): ?>
                                                    en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                                <?php endif; ?>
                                            </p>
                                            <div class="estrellas"><?= estrellasPerfil($resena['puntuacion_estrellas']) ?></div>
                                        </div>
                                        <p class="fecha"><?= fechaPerfilBonita($resena['fecha_publicacion']) ?></p>
                                    </div>
                                    <p class="texto"><?= htmlspecialchars($textoCorto) ?><?php if ($textoCompleto !== ''): ?><span class="completo"><?= htmlspecialchars($textoCompleto) ?></span><?php endif; ?></p>
                                    <p class="username"><?= htmlspecialchars($datosUsuario['nick']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2>Todavía no has publicado reseñas</h2>
                            <p>Cuando completes tu primera reseña desde un juego guardado en biblioteca, aparecerá aquí.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
