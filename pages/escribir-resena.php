<?php
require '../api/cache.php';
require '../includes/auth.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$idIgdb = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);
$idUsuario = (int) getUsuario()['id'];
$juego = $idIgdb > 0 ? cacheDetalleJuego($db, $idIgdb, $idUsuario) : null;

if (!$juego) {
    http_response_code(404);
}

if ($juego && empty($juego['usuario_juego'])) {
    header('Location: /registrar-juego.php?id=' . $idIgdb);
    exit;
}

$resenaUsuario = $juego ? cacheResenaUsuario($db, $idUsuario, (int) $juego['id']) : null;

if ($resenaUsuario && !empty($resenaUsuario['tiene_comentario'])) {
    header('Location: /editar-resena.php?id=' . $idIgdb);
    exit;
}

$error = '';
$puntuacion = '';
$comentario = '';

if ($resenaUsuario && !empty($resenaUsuario['puntuacion'])) {
    $puntuacion = (string) (int) $resenaUsuario['puntuacion'];
} elseif ($juego && isset($juego['usuario_juego']['puntuacion_usuario']) && $juego['usuario_juego']['puntuacion_usuario'] !== null) {
    $puntuacion = (string) (int) round(((float) $juego['usuario_juego']['puntuacion_usuario']) * 20);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $juego) {
    $puntuacion = trim((string) ($_POST['puntuacion'] ?? ''));
    $comentario = trim((string) ($_POST['comentario'] ?? ''));

    if (!ctype_digit($puntuacion) || !cachePuntuacionResenaValida((int) $puntuacion)) {
        $error = 'Selecciona una puntuación válida';
    } elseif (!cacheComentarioResenaValido($comentario)) {
        $error = 'El comentario debe tener entre 20 y 2000 caracteres';
    } elseif (!cacheGuardarResenaUsuario($db, $idUsuario, (int) $juego['id'], (int) $puntuacion, $comentario)) {
        $error = 'No se ha podido guardar la reseña ahora mismo';
    } else {
        header('Location: /juego.php?id=' . $idIgdb . '&resena=ok');
        exit;
    }
}

$titulo = $juego ? ('Escribir reseña — ' . $juego['titulo'] . ' — LogNow!') : 'Juego no encontrado — LogNow!';
$css = ['biblioteca.css', 'escribir-resena.css'];
$pagina = 'mis-juegos';
$js = ['puntuacion.js', 'resena-form.js'];
require '../includes/header.php';
?>

<main class="container">
    <?php if ($juego): ?>
        <section class="cabecera-biblioteca cabecera-resena">
            <div>
                <p class="eyebrow">Reseñas</p>
                <h1>Escribir reseña</h1>
                <p class="texto-cabecera">Completa tu valoración de este juego con una opinión más personal y detallada.</p>
            </div>
            <a class="boton-secundario" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Volver a la ficha</a>
        </section>

        <div class="bloque-biblioteca bloque-resena">
            <section class="resumen-juego-biblioteca resumen-resena">
                <div class="portada-resumen">
                    <img src="<?= htmlspecialchars(urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                </div>
                <div class="datos-resumen">
                    <p class="eyebrow">Juego seleccionado</p>
                    <h2><?= htmlspecialchars($juego['titulo']) ?></h2>
                    <p class="subtexto-resumen">
                        <?php if (!empty($juego['desarrolladora'])): ?>
                            <?= htmlspecialchars($juego['desarrolladora']) ?>
                        <?php else: ?>
                            Catálogo de LogNow!
                        <?php endif; ?>
                    </p>
                    <p class="meta-resumen">Estado: <?= htmlspecialchars(ucfirst($juego['usuario_juego']['estado'])) ?></p>
                    <p class="meta-resumen">Plataforma: <?= htmlspecialchars($juego['usuario_juego']['plataforma'] ?? 'Sin plataforma') ?></p>
                </div>
            </section>

            <section class="formulario-biblioteca formulario-resena">
                <?php if ($error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" id="form-resena" novalidate>
                    <input type="hidden" name="id" value="<?= (int) $juego['igdb_id'] ?>">

                    <div class="grid-formulario-resena">
                        <div class="campo campo-puntuacion campo-puntuacion-resena">
                            <span class="label-puntuacion">Tu puntuación</span>
                            <div class="selector-puntuacion" id="selector-puntuacion">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="estrella-puntuacion" data-estrella="<?= $i ?>" tabindex="0" role="button" aria-label="<?= $i ?> estrellas">
                                        <i class="fa-regular fa-star"></i>
                                    </span>
                                <?php endfor; ?>
                            </div>
                            <div class="fila-puntuacion">
                                <p class="texto-puntuacion" id="texto-puntuacion">Sin puntuar</p>
                                <button type="button" class="limpiar-puntuacion" id="limpiar-puntuacion">Quitar</button>
                            </div>
                            <input type="hidden" id="puntuacion" name="puntuacion" value="<?= htmlspecialchars($puntuacion) ?>">
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo campo-comentario">
                            <label for="comentario">Tu comentario</label>
                            <textarea id="comentario" name="comentario" rows="10" maxlength="2000" placeholder="Cuéntanos qué te ha parecido este juego, qué destaca y qué te ha dejado peor sabor de boca si lo tiene."><?= htmlspecialchars($comentario) ?></textarea>
                            <div class="meta-comentario">
                                <span class="ayuda-comentario">Entre 20 y 2000 caracteres</span>
                                <span class="contador-comentario" id="contador-comentario">0/2000</span>
                            </div>
                            <span class="msg-error"></span>
                        </div>
                    </div>

                    <div class="acciones-formulario">
                        <button type="submit">Publicar reseña</button>
                        <a class="boton-secundario" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    <?php else: ?>
        <section class="juego-vacio">
            <h1>Juego no encontrado</h1>
            <p>Este juego todavía no está disponible en el catálogo local de LogNow!.</p>
        </section>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
