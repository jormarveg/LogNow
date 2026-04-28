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

function alturaBarraPerfil($valor, $maximo) {
    if ($maximo <= 0) {
        return 12;
    }

    return max(12, (int) round(($valor / $maximo) * 100));
}

$tab = $_GET['tab'] ?? 'perfil';
$tabsValidas = ['perfil', 'juegos', 'resenas'];

if (!in_array($tab, $tabsValidas, true)) {
    header('Location: /perfil.php');
    exit;
}

$estadoFiltro = $_GET['estado'] ?? '';
$paginaBibliotecaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPaginaBiblioteca = 12;
$estadosValidos = ['jugando', 'completado', 'pendiente', 'abandonado'];

if (!in_array($estadoFiltro, $estadosValidos, true)) {
    $estadoFiltro = '';
}

$datosUsuario = $usuarioModel->obtenerPorId(getUsuario()['id']);
$idUsuario = (int) getUsuario()['id'];
$resumenBiblioteca = cacheResumenBibliotecaUsuario($db, (int) getUsuario()['id']);
$totalJuegosBiblioteca = cacheContarBibliotecaUsuario($db, $idUsuario, $estadoFiltro);
$totalPaginasBiblioteca = max(1, (int) ceil($totalJuegosBiblioteca / $porPaginaBiblioteca));

if ($paginaBibliotecaActual > $totalPaginasBiblioteca) {
    $paginaBibliotecaActual = $totalPaginasBiblioteca;
}

$offsetBiblioteca = ($paginaBibliotecaActual - 1) * $porPaginaBiblioteca;
$juegosBiblioteca = cacheListarBibliotecaUsuario($db, $idUsuario, $estadoFiltro, $porPaginaBiblioteca, $offsetBiblioteca);
$resenasUsuarioPerfil = cacheListarResenasUsuario($db, $idUsuario, 5);
$resenasUsuarioTab = cacheListarResenasUsuario($db, $idUsuario, 20);
$favoritosUsuario = cacheFavoritosUsuario($db, $idUsuario, 6);
$jugadosEsteAno = cacheJuegosUsuarioEsteAno($db, $idUsuario);
$histogramaUsuario = cacheHistogramaUsuario($db, $idUsuario);
$maximoHistograma = max($histogramaUsuario ?: [0]);
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

<section class="encabezado-perfil" style="background-image: url('<?= htmlspecialchars(urlEncabezadoUsuario($datosUsuario['encabezado'] ?? '')) ?>');">
    <div class="container">
        <div class="foto-perfil">
            <img src="<?= htmlspecialchars(urlAvatarUsuario($datosUsuario['avatar'] ?? '')) ?>" alt="Foto de perfil de <?= htmlspecialchars($datosUsuario['nick']) ?>">
        </div>
        <div class="datos-encabezado-perfil">
            <h1 class="nombre"><?= htmlspecialchars($datosUsuario['nombre']) ?></h1>
            <p class="nick-perfil">@<?= htmlspecialchars($datosUsuario['nick']) ?></p>
            <a class="boton-editar-perfil" href="/editar-perfil.php">Editar perfil</a>
        </div>
    </div>
</section>

<nav class="profile-tabs">
    <div class="container">
        <ul>
            <li<?= $tab === 'perfil' ? ' class="active"' : '' ?>><a href="/perfil.php"><i class="fa-solid fa-user"></i>Perfil</a></li>
            <li<?= $tab === 'juegos' ? ' class="active"' : '' ?>><a href="/perfil.php?tab=juegos"><i class="fa-solid fa-gamepad"></i>Tus juegos</a></li>
            <li<?= $tab === 'resenas' ? ' class="active"' : '' ?>><a href="/perfil.php?tab=resenas"><i class="fa-solid fa-message"></i>Tus reseñas</a></li>
            <li><a class="editar" href="/editar-perfil.php">Editar perfil</a></li>
        </ul>
    </div>
</nav>

<main class="container">
    <div class="content-grid">
        <aside class="sidebar">
            <section class="bio">
                <h2>Bio</h2>
                <p><?= $datosUsuario['biografia'] ? nl2br(htmlspecialchars($datosUsuario['biografia'])) : 'Todavía no has escrito ninguna bio.' ?></p>
            </section>

            <section class="stats">
                <div class="jugados">
                    <h3>Jugados</h3>
                    <span class="datos"><?= $resumenBiblioteca['total'] ?></span>
                </div>
                <div class="este-ano">
                    <h3>Este año</h3>
                    <span class="datos"><?= $jugadosEsteAno ?></span>
                </div>
            </section>

            <section class="puntuaciones">
                <h3>Tus puntuaciones</h3>
                <div class="grafica">
                    <div class="barra barra1" style="height: <?= alturaBarraPerfil($histogramaUsuario[1], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra2" style="height: <?= alturaBarraPerfil($histogramaUsuario[2], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra3" style="height: <?= alturaBarraPerfil($histogramaUsuario[3], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra4" style="height: <?= alturaBarraPerfil($histogramaUsuario[4], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra5" style="height: <?= alturaBarraPerfil($histogramaUsuario[5], $maximoHistograma) ?>%;"></div>
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
            <?php if (isset($_GET['editado']) && $_GET['editado'] === 'ok'): ?>
                <p class="exito exito-perfil">Perfil actualizado correctamente.</p>
            <?php endif; ?>

            <?php if ($tab === 'perfil'): ?>
                <section class="favoritos">
                    <h2>Tus favoritos</h2>
                    <?php if ($favoritosUsuario): ?>
                        <div class="carousel">
                            <?php foreach ($favoritosUsuario as $favorito): ?>
                                <a class="favorito elemento-carousel" href="/juego.php?id=<?= (int) $favorito['igdb_id'] ?>">
                                    <div class="portada"><img src="<?= htmlspecialchars(urlPortadaJuego($favorito['portada_url'] ?? '', $favorito['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($favorito['titulo']) ?>"></div>
                                    <p><?= htmlspecialchars($favorito['titulo']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2>Todavía no tienes favoritos</h2>
                            <p>Marca juegos de tu biblioteca como favoritos y aparecerán aquí.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="resenas-recientes">
                    <h2>Tus reseñas recientes</h2>
                    <?php if ($resenasUsuarioPerfil): ?>
                        <div class="carousel">
                            <?php foreach ($resenasUsuarioPerfil as $resena): ?>
                                <?php [$textoCorto, $textoCompleto] = partesTextoPerfilResena($resena['comentario']); ?>
                                <div class="elemento-carousel mini-resena">
                                    <div class="mini-portada"><img src="<?= htmlspecialchars(urlPortadaJuego($resena['portada_url'] ?? '', $resena['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
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
                                    <div class="mini-portada"><img src="<?= htmlspecialchars(urlPortadaJuego($resena['portada_url'] ?? '', $resena['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
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
