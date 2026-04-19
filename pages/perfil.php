<?php
require '../api/cache.php';
require '../includes/auth.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

function textoEstadoPerfil($estado) {
    $estados = [
        'jugando' => 'Jugando',
        'completado' => 'Completado',
        'pendiente' => 'Pendiente',
        'abandonado' => 'Abandonado'
    ];

    return $estados[$estado] ?? 'Sin estado';
}

function tiempoPerfil($horas, $minutos) {
    $horas = (int) $horas;
    $minutos = (int) $minutos;

    if ($horas <= 0 && $minutos <= 0) {
        return 'Sin tiempo registrado';
    }

    if ($horas > 0 && $minutos > 0) {
        return $horas . ' h ' . $minutos . ' min';
    }

    if ($horas > 0) {
        return $horas . ' h';
    }

    return $minutos . ' min';
}

function fechaPerfilBonita($fecha) {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

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
$resumenBiblioteca = cacheResumenBibliotecaUsuario($db, (int) getUsuario()['id']);
$juegosBiblioteca = cacheListarBibliotecaUsuario($db, (int) getUsuario()['id'], $estadoFiltro);
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
                    <div class="carousel">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="elemento-carousel mini-resena">
                            <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <div class="nombre-puntuacion">
                                <h4>Juego 1</h4>
                                <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                            </div>
                            <div class="puntuacion-tablet">
                                <div class="titulo-puntuacion-wrapper">
                                    <p class="titulo-plataforma"><strong>Juego 1</strong> en <strong>Nintendo Switch</strong></p>
                                    <div class="estrellas">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                                    </div>
                                </div>
                                <p class="fecha">22 febrero 2026</p>
                            </div>
                            <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt
                                tenetur? Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                            </p>
                            <p class="username">Usuario1</p>
                        </div>
                        <?php endfor; ?>
                    </div>
                </section>
            <?php elseif ($tab === 'juegos'): ?>
                <section class="cabecera-biblioteca">
                    <div>
                        <p class="eyebrow">Biblioteca personal</p>
                        <h2>Mis juegos</h2>
                        <p class="texto-cabecera">Consulta tu biblioteca, filtra por estado y revisa rápido lo que tienes en marcha.</p>
                    </div>
                    <a class="boton-principal" href="/catalogo.php">Registrar otro juego</a>
                </section>

                <section class="tarjetas-resumen">
                    <article class="tarjeta-resumen">
                        <span class="valor-resumen"><?= $resumenBiblioteca['total'] ?></span>
                        <span class="label-resumen">Totales</span>
                    </article>
                    <article class="tarjeta-resumen">
                        <span class="valor-resumen"><?= $resumenBiblioteca['favoritos'] ?></span>
                        <span class="label-resumen">Favoritos</span>
                    </article>
                    <article class="tarjeta-resumen">
                        <span class="valor-resumen"><?= $resumenBiblioteca['jugando'] ?></span>
                        <span class="label-resumen">Jugando</span>
                    </article>
                    <article class="tarjeta-resumen">
                        <span class="valor-resumen"><?= $resumenBiblioteca['completados'] ?></span>
                        <span class="label-resumen">Completados</span>
                    </article>
                </section>

                <nav class="filtros-biblioteca">
                    <?php foreach ($filtros as $clave => $texto): ?>
                        <a href="<?= htmlspecialchars(urlPerfilTab('juegos', ['estado' => $clave])) ?>"<?= $estadoFiltro === $clave ? ' class="active"' : '' ?>>
                            <span><?= $texto ?></span>
                            <strong><?= $contadorFiltros[$clave] ?? 0 ?></strong>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <section class="grid-biblioteca">
                    <?php if ($juegosBiblioteca): ?>
                        <?php foreach ($juegosBiblioteca as $juego): ?>
                            <article class="tarjeta-biblioteca">
                                <a class="portada-biblioteca" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">
                                    <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                                    <?php if (!empty($juego['favorito'])): ?>
                                        <span class="favorito-biblioteca"><i class="fa-solid fa-heart"></i></span>
                                    <?php endif; ?>
                                </a>

                                <div class="contenido-biblioteca">
                                    <div class="cabecera-tarjeta-biblioteca">
                                        <h2><a href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>"><?= htmlspecialchars($juego['titulo']) ?></a></h2>
                                        <span class="estado-biblioteca estado-<?= htmlspecialchars($juego['estado']) ?>"><?= textoEstadoPerfil($juego['estado']) ?></span>
                                    </div>

                                    <p class="plataforma-biblioteca"><?= htmlspecialchars($juego['plataforma']) ?></p>

                                    <div class="meta-biblioteca">
                                        <p><?= tiempoPerfil($juego['horas_jugadas'], $juego['minutos_jugados']) ?></p>
                                        <p>Inicio: <?= fechaPerfilBonita($juego['fecha_inicio']) ?></p>
                                        <?php if ($juego['estado'] === 'completado' && !empty($juego['fecha_fin'])): ?>
                                            <p>Fin: <?= fechaPerfilBonita($juego['fecha_fin']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="pie-biblioteca">
                                        <?php if ($juego['puntuacion_usuario'] !== null): ?>
                                            <p class="nota-biblioteca"><i class="fa-solid fa-star"></i> <?= puntuacionPerfilVisible($juego['puntuacion_usuario']) ?></p>
                                        <?php else: ?>
                                            <p class="nota-biblioteca sin-nota">Sin reseña todavía</p>
                                        <?php endif; ?>

                                        <a class="enlace-detalle-biblioteca" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Ver ficha</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <?php if ($resumenBiblioteca['total'] > 0): ?>
                                <h2>No hay juegos con ese filtro</h2>
                                <p>Prueba a cambiar el estado seleccionado para ver el resto de tu biblioteca.</p>
                            <?php else: ?>
                                <h2>Tu biblioteca está vacía</h2>
                                <p>Todavía no has registrado ningún juego. Empieza desde el catálogo y guarda el primero.</p>
                            <?php endif; ?>
                            <a class="boton-secundario" href="/catalogo.php">Ir al catálogo</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php else: ?>
                <section class="resenas-recientes">
                    <h2>Tus reseñas recientes</h2>
                    <div class="carousel">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <div class="elemento-carousel mini-resena">
                            <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                            <div class="nombre-puntuacion">
                                <h4>Juego 1</h4>
                                <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                            </div>
                            <div class="puntuacion-tablet">
                                <div class="titulo-puntuacion-wrapper">
                                    <p class="titulo-plataforma"><strong>Juego 1</strong> en <strong>Nintendo Switch</strong></p>
                                    <div class="estrellas">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i><i
                                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                                    </div>
                                </div>
                                <p class="fecha">22 febrero 2026</p>
                            </div>
                            <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt
                                tenetur? Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                            </p>
                            <p class="username">Usuario1</p>
                        </div>
                        <?php endfor; ?>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
