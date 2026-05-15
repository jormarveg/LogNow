<?php
require_once __DIR__ . '/api/cache.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/perfil_helpers.php';

function fechaInicioBonita($fecha) {
    if (!$fecha) {
        return '';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    return date('j', $marca) . ' ' . $meses[(int) date('n', $marca) - 1] . ' ' . date('Y', $marca);
}

function puntuacionInicioVisible($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function estrellasInicio($puntuacion) {
    if ($puntuacion === null) {
        $puntuacion = 0;
    }

    $puntuacion = max(0, min(5, (float) $puntuacion));
    $completas = (int) floor($puntuacion);
    $media = ($puntuacion - $completas) >= 0.5;
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $completas) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($media && $i === $completas + 1) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-solid fa-star vacia"></i>';
        }
    }

    return $html;
}

function partesTextoInicioResena($texto, $limite = 110) {
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

function htmlCarruselInicioJuegos($idCarrusel, $juegos) {
    ob_start(); ?>
    <div class="carousel-wrapper">
        <div id="<?= htmlspecialchars($idCarrusel) ?>" class="f-carousel carousel-juegos-home">
            <?php foreach ($juegos as $juego): ?>
                <article class="f-carousel__slide elemento-carousel">
                    <div class="portada">
                        <a href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>" aria-label="Ver ficha de <?= htmlspecialchars($juego['titulo']) ?>">
                            <img src="<?= htmlspecialchars(urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                        </a>
                        <div class="puntuacion">
                            <i class="fa-solid fa-star"></i>
                            <span><?= puntuacionInicioVisible($juego['puntuacion_visible']) ?></span>
                        </div>
                    </div>
                    <div class="titulo-puntuacion">
                        <a class="titulo-juego" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>"><?= htmlspecialchars($juego['titulo']) ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

    return trim((string) ob_get_clean());
}

$resenasRecientes = cacheResenasRecientesInicio($db, 4);
$juegosTendencia = cacheJuegosTendenciaInicio($db, 10);
$recomendacionesInicio = [];
$totalBibliotecaInicio = 0;

if (estaLogueado()) {
    $idUsuarioInicio = (int) (getUsuario()['id'] ?? 0);
    $resumenInicio = cacheResumenBibliotecaUsuario($db, $idUsuarioInicio);
    $totalBibliotecaInicio = (int) ($resumenInicio['total'] ?? 0);
    $recomendacionesInicio = cacheRecomendacionesInicio($db, $idUsuarioInicio, 10);
}

$titulo = 'Inicio — LogNow!';
$css = ['resenas.css', 'index.css'];
$cssExterno = [
    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/carousel/carousel.css',
    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.arrows.css'
];
$jsExterno = [
    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.umd.js',
    'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/carousel/carousel.arrows.umd.js'
];
$js = ['carrusel.js'];
$usarJquery = true;
$pagina = 'inicio';
require 'includes/header.php';
?>

<main class="container">
    <?php if ($juegosTendencia): ?>
        <section class="tendencias seccion-juegos-home">
            <h2 class="titulo-mobile">Tendencias</h2>
            <h2 class="titulo-tablet">Juegos en tendencia</h2>
            <?= htmlCarruselInicioJuegos('carouselTendencias', $juegosTendencia) ?>
        </section>
    <?php endif; ?>

    <section class="resenas-recientes resenas-home">
        <h2>Reseñas recientes</h2>
        <?php if ($resenasRecientes): ?>
            <div class="carousel">
                <?php foreach ($resenasRecientes as $resena): ?>
                    <?php [$textoCorto, $textoCompleto] = partesTextoInicioResena($resena['comentario']); ?>
                    <article class="elemento-carousel mini-resena">
                        <div class="mini-portada">
                            <a href="/juego.php?id=<?= (int) $resena['igdb_id'] ?>" aria-label="Ver ficha de <?= htmlspecialchars($resena['titulo']) ?>">
                                <img src="<?= htmlspecialchars(urlPortadaJuego($resena['portada_url'] ?? '', $resena['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>">
                            </a>
                        </div>
                        <div class="nombre-puntuacion">
                            <h4><a href="/juego.php?id=<?= (int) $resena['igdb_id'] ?>"><?= htmlspecialchars($resena['titulo']) ?></a></h4>
                            <div class="puntuacion"><i class="fa-solid fa-star"></i><span><?= puntuacionInicioVisible($resena['puntuacion_estrellas']) ?></span></div>
                        </div>
                        <div class="puntuacion-tablet">
                            <div class="titulo-puntuacion-wrapper">
                                <p class="titulo-plataforma">
                                    <strong><a href="/juego.php?id=<?= (int) $resena['igdb_id'] ?>"><?= htmlspecialchars($resena['titulo']) ?></a></strong>
                                    <?php if (!empty($resena['plataforma'])): ?>
                                        en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                    <?php endif; ?>
                                </p>
                                <div class="meta-resena-inline">
                                    <div class="estrellas"><?= estrellasInicio($resena['puntuacion_estrellas']) ?></div>
                                    <span>por <a href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>"><?= htmlspecialchars($resena['nick']) ?></a></span>
                                </div>
                            </div>
                            <p class="fecha"><?= fechaInicioBonita($resena['fecha_publicacion']) ?></p>
                        </div>
                        <p class="texto">
                            <?= htmlspecialchars($textoCorto) ?>
                            <?php if ($textoCompleto !== ''): ?>
                                <span class="completo"><?= htmlspecialchars($textoCompleto) ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="username"><a href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>"><?= htmlspecialchars($resena['nick']) ?></a></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Todavía no hay reseñas recientes publicadas en LogNow!.</p>
        <?php endif; ?>
    </section>

    <?php if (estaLogueado()): ?>
        <section class="recomendados-home seccion-juegos-home">
            <h2 class="titulo-mobile">Recomendados</h2>
            <h2 class="titulo-tablet">Recomendados para ti</h2>
            <?php if ($recomendacionesInicio): ?>
                <?= htmlCarruselInicioJuegos('carouselRecomendaciones', $recomendacionesInicio) ?>
            <?php else: ?>
                <div class="panel-vacio-juegos-home">
                    <p><?= $totalBibliotecaInicio < 3 ? 'Añade más juegos a tu biblioteca para recibir recomendaciones.' : 'Todavía no hay recomendaciones para ti.' ?></p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<?php
require 'includes/nav_inferior.php';
require 'includes/footer.php';
?>
