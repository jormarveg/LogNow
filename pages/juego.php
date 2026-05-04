<?php
require '../api/cache.php';
require '../includes/auth.php';

$idIgdb = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$idUsuario = estaLogueado() ? (int) getUsuario()['id'] : 0;
$juego = $idIgdb > 0 ? cacheDetalleJuego($db, $idIgdb, $idUsuario) : null;

if (!$juego) {
    http_response_code(404);
}

if (
    $juego
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && estaLogueado()
    && !empty($juego['usuario_juego'])
) {
    $nuevoEstado = $_POST['estado_juego'] ?? '';
    $estadosCambio = ['completado', 'jugando', 'pendiente', 'abandonado'];

    if (isset($_POST['toggle_favorito'])) {
        cacheActualizarFavoritoJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], !$juego['usuario_juego']['favorito']);
    } elseif (in_array($nuevoEstado, $estadosCambio, true)) {
        cacheActualizarEstadoJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], $nuevoEstado);
    }

    header('Location: /juego.php?id=' . $idIgdb);
    exit;
}

function fechaBonita($fecha, $abreviada = false) {
    if (!$fecha) {
        return null;
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = $abreviada
        ? ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
        : ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    $mes = $meses[(int) date('n', $marca) - 1];

    return date('j', $marca) . ' ' . $mes . ' ' . date('Y', $marca);
}

function puntuacionVisible($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function estrellasJuego($puntuacion) {
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

$estados = [
    'completado' => ['icono' => 'fa-check', 'texto' => 'Complet.'],
    'jugando' => ['icono' => 'fa-gamepad', 'texto' => 'Jugando'],
    'pendiente' => ['icono' => 'fa-calendar-days', 'texto' => 'Pendiente'],
    'abandonado' => ['icono' => 'fa-ban', 'texto' => 'Aband.']
];

$background = $juego['background_url'] ?? '/assets/img/profile/banner.webp';
$portada = $juego['portada_url'] ?? '/assets/img/covers/expedition33.jpg';
$estadoActual = $juego['usuario_juego']['estado'] ?? '';
$favorito = !empty($juego['usuario_juego']['favorito']);
$puntuacionUsuario = $juego['usuario_juego']['puntuacion_usuario'] ?? null;
$plataformaUsuario = $juego['usuario_juego']['plataforma'] ?? '';
$puntuacionMedia = $juego['resumen_resenas']['media'] ?? null;
$totalResenas = $juego['resumen_resenas']['total'] ?? 0;
$histograma = $juego['histograma'] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$maxHistograma = max($histograma);
$mensajeBiblioteca = $_GET['biblioteca'] ?? '';
$generos = $juego ? implode(' · ', $juego['generos']) : '';
$plataformas = $juego ? implode(' · ', $juego['plataformas']) : '';
$titulo = $juego ? $juego['titulo'] . ' — LogNow!' : 'Juego no encontrado — LogNow!';
$pagina = 'catalogo';
$css = ['resenas.css', 'juego.css'];
$js = ['juego.js'];
$usarJquery = true;
require '../includes/header.php';
?>

<?php if ($juego): ?>
    <section class="encabezado-juego" style="background-image: url('<?= htmlspecialchars($background) ?>');">
        <div class="container cabecera-juego">
            <div class="portada-juego">
                <img src="<?= htmlspecialchars($portada) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                <?php if (estaLogueado()): ?>
                    <?php if ($estadoActual): ?>
                        <form class="favorito-form" method="POST">
                            <input type="hidden" name="toggle_favorito" value="1">
                            <input type="hidden" name="id_videojuego" value="<?= (int) $juego['id'] ?>">
                            <input type="hidden" name="favorito" value="<?= $favorito ? '1' : '0' ?>">
                            <button type="submit" class="favorito-juego<?= $favorito ? ' active' : '' ?>" aria-label="<?= $favorito ? 'Quitar de favoritos' : 'Marcar como favorito' ?>">
                                <i class="<?= $favorito ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="favorito-juego" aria-hidden="true">
                            <i class="fa-regular fa-heart"></i>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="datos-principales">
                <h1><?= htmlspecialchars($juego['titulo']) ?></h1>
                <p class="subtitulo">
                    <?php if (!empty($juego['desarrolladora'])): ?>
                        por <strong><?= htmlspecialchars($juego['desarrolladora']) ?></strong>
                    <?php else: ?>
                        Juego de LogNow!
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </section>

    <main class="container">
        <div class="content-grid">
            <?php if ($mensajeBiblioteca === 'ok'): ?>
                <p class="mensaje-juego exito">Juego añadido correctamente a tu biblioteca.</p>
            <?php elseif ($mensajeBiblioteca === 'editado'): ?>
                <p class="mensaje-juego exito">Datos del juego actualizados correctamente.</p>
            <?php elseif ($mensajeBiblioteca === 'existe'): ?>
                <p class="mensaje-juego aviso">Ese juego ya estaba guardado en tu biblioteca.</p>
            <?php endif; ?>

            <aside class="sidebar">
                <nav class="estados-juego">
                    <?php foreach ($estados as $clave => $estado): ?>
                        <?php if (estaLogueado() && $estadoActual): ?>
                            <form class="estado-form" method="POST">
                                <input type="hidden" name="id_videojuego" value="<?= (int) $juego['id'] ?>">
                                <input type="hidden" name="estado_juego" value="<?= $clave ?>">
                                <button type="submit" class="estado estado-boton<?= $estadoActual === $clave ? ' active' : '' ?>">
                                    <i class="fa-solid <?= $estado['icono'] ?>"></i>
                                    <span><?= $estado['texto'] ?></span>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="estado<?= $estadoActual === $clave ? ' active' : '' ?>">
                                <i class="fa-solid <?= $estado['icono'] ?>"></i>
                                <span><?= $estado['texto'] ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <section class="puntuaciones">
                    <div class="tu-puntuacion">
                        <h2>Tu puntuación</h2>
                        <?php if (estaLogueado()): ?>
                            <p class="numero-puntuacion"><?= $puntuacionUsuario !== null ? puntuacionVisible($puntuacionUsuario) : 'N/D' ?></p>
                            <div class="estrellas"><?= estrellasJuego($puntuacionUsuario) ?></div>
                            <p class="nota-usuario">
                                <?php if ($plataformaUsuario): ?>
                                    En <?= htmlspecialchars($plataformaUsuario) ?>
                                <?php else: ?>
                                    <?= $puntuacionUsuario !== null ? 'Con reseña guardada' : 'Sin puntuar todavía' ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!$estadoActual): ?>
                                <a class="cta-biblioteca" href="/registrar-juego.php?id=<?= (int) $juego['igdb_id'] ?>">Añadir a mi biblioteca</a>
                            <?php else: ?>
                                <a class="cta-biblioteca secundaria" href="/registrar-juego.php?id=<?= (int) $juego['igdb_id'] ?>&editar=1">Editar</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="numero-puntuacion">N/D</p>
                            <div class="estrellas">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star vacia"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="nota-usuario">Inicia sesión para guardar tu estado y tu puntuación</p>
                            <a class="cta-biblioteca secundaria" href="/login.php">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>

                    <div class="media-juego">
                        <h3>Puntuación media</h3>
                        <p class="numero-puntuacion"><?= puntuacionVisible($puntuacionMedia) ?></p>
                        <div class="grafica">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php $altura = $maxHistograma > 0 ? max(10, (int) round(($histograma[$i] / $maxHistograma) * 100)) : 0; ?>
                                <div class="barra"<?= $altura > 0 ? ' style="height: ' . $altura . '%"' : '' ?>></div>
                            <?php endfor; ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span><?= $i ?> <i class="fa-solid fa-star"></i></span>
                            <?php endfor; ?>
                        </div>
                        <p class="total-resenas"><?= $totalResenas ?> reseñas</p>
                    </div>
                </section>
            </aside>

            <hr class="separador">

            <section class="principal">
                <section class="informacion-juego">
                    <div class="metadatos-juego">
                        <div class="lanzamiento">
                            <span>Lanzamiento</span>
                            <strong><?= fechaBonita($juego['fecha_lanzamiento']) ?: 'Sin fecha' ?></strong>
                        </div>
                        <div class="datos-juego">
                            <p><strong>Géneros</strong> <?= $generos ?: 'Sin datos' ?></p>
                            <p><strong>Plataformas</strong> <?= $plataformas ?: 'Sin datos' ?></p>
                        </div>
                    </div>

                    <p class="descripcion-juego">
                        <?= nl2br(htmlspecialchars($juego['descripcion'] ?: 'Este juego ya forma parte del catálogo de LogNow!, pero todavía no tiene una descripción amplia guardada en la base local.')) ?>
                    </p>
                </section>

                <section class="resenas-recientes resenas-juego">
                    <h2>Reseñas</h2>

                    <?php if (!empty($juego['resenas'])): ?>
                        <div class="carousel">
                            <?php foreach ($juego['resenas'] as $resena): ?>
                                <article class="elemento-carousel mini-resena">
                                    <div class="mini-portada">
                                        <img src="<?= htmlspecialchars($resena['avatar'] ?: '/assets/img/profile/user.webp') ?>" alt="Avatar de <?= htmlspecialchars($resena['nick']) ?>">
                                    </div>
                                    <div class="nombre-puntuacion">
                                        <h4><?= htmlspecialchars($resena['nick']) ?></h4>
                                        <div class="puntuacion">
                                            <i class="fa-solid fa-star"></i>
                                            <span><?= puntuacionVisible($resena['puntuacion_estrellas']) ?></span>
                                        </div>
                                    </div>
                                    <div class="puntuacion-tablet">
                                        <div class="titulo-puntuacion-wrapper">
                                            <p class="titulo-plataforma">
                                                <strong><?= htmlspecialchars($juego['titulo']) ?></strong>
                                                <?php if (!empty($resena['plataforma'])): ?>
                                                    en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                                <?php endif; ?>
                                            </p>
                                            <div class="estrellas"><?= estrellasJuego($resena['puntuacion_estrellas']) ?></div>
                                        </div>
                                        <p class="fecha"><?= fechaBonita($resena['fecha_publicacion'], true) ?></p>
                                    </div>
                                    <?php if (!empty(trim((string) $resena['comentario']))): ?>
                                        <p class="texto"><?= nl2br(htmlspecialchars($resena['comentario'])) ?></p>
                                    <?php endif; ?>
                                    <p class="username"><?= htmlspecialchars($resena['nick']) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="sin-resenas">
                            <p>Todavía no hay reseñas publicadas para este juego en LogNow!.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </div>
    </main>
<?php else: ?>
    <main class="container">
        <section class="juego-vacio">
            <h1>Juego no encontrado</h1>
            <p>Este juego todavía no está disponible en el catálogo local de LogNow!.</p>
        </section>
    </main>
<?php endif; ?>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
