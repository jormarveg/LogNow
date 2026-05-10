<?php
require_once __DIR__ . '/api/cache.php';
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

$resenasRecientes = cacheResenasRecientesInicio($db, 4);
$juegosTendencia = cacheJuegosTendenciaInicio($db, 8);
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
        <section class="tendencias">
            <h2 class="titulo-mobile">Tendencias</h2>
            <h2 class="titulo-tablet">Juegos en tendencia</h2>
            <div class="carousel-wrapper">
                <div id="carouselTendencias" class="f-carousel carousel-tendencias">
                    <?php foreach ($juegosTendencia as $juegoTendencia): ?>
                        <article class="f-carousel__slide elemento-carousel">
                            <div class="portada">
                                <a href="/juego.php?id=<?= (int) $juegoTendencia['igdb_id'] ?>" aria-label="Ver ficha de <?= htmlspecialchars($juegoTendencia['titulo']) ?>">
                                    <img src="<?= htmlspecialchars(urlPortadaJuego($juegoTendencia['portada_url'] ?? '', $juegoTendencia['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juegoTendencia['titulo']) ?>">
                                </a>
                                <div class="puntuacion">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= puntuacionInicioVisible($juegoTendencia['puntuacion_visible']) ?></span>
                                </div>
                            </div>
                            <div class="titulo-puntuacion">
                                <a class="titulo-juego" href="/juego.php?id=<?= (int) $juegoTendencia['igdb_id'] ?>"><?= htmlspecialchars($juegoTendencia['titulo']) ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="resenas-recientes">
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
</main>

<?php
require 'includes/nav_inferior.php';
require 'includes/footer.php';
?>
