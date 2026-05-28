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

if (!$resenaUsuario || empty($resenaUsuario['tiene_comentario'])) {
    header('Location: /escribir-resena.php?id=' . $idIgdb);
    exit;
}

$error = '';
$puntuacion = (string) (int) ($resenaUsuario['puntuacion'] ?? 0);
$comentario = trim((string) ($resenaUsuario['comentario'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $juego) {
    $accion = $_POST['accion'] ?? 'guardar';

    if ($accion === 'eliminar_resena') {
        if (cacheEliminarResenaUsuario($db, $idUsuario, (int) $juego['id'])) {
            header('Location: /juego.php?id=' . $idIgdb . '&resena=eliminada');
            exit;
        }

        $error = 'No se ha podido eliminar la reseña ahora mismo';
    } else {
        $puntuacion = trim((string) ($_POST['puntuacion'] ?? ''));
        $comentario = trim((string) ($_POST['comentario'] ?? ''));

        if (!ctype_digit($puntuacion) || !cachePuntuacionResenaValida((int) $puntuacion)) {
            $error = 'Selecciona una puntuación válida';
        } elseif (!cacheComentarioResenaValido($comentario)) {
            $error = 'El comentario debe tener entre 20 y 2000 caracteres';
        } elseif (!cacheActualizarResenaUsuario($db, $idUsuario, (int) $juego['id'], (int) $puntuacion, $comentario)) {
            $error = 'No se ha podido actualizar la reseña ahora mismo';
        } else {
            header('Location: /juego.php?id=' . $idIgdb . '&resena=actualizada');
            exit;
        }
    }
}

$titulo = $juego ? ('Editar reseña — ' . $juego['titulo'] . ' — LogNow!') : 'Juego no encontrado — LogNow!';
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
                <h1>Editar reseña</h1>
                <p class="texto-cabecera">Ajusta tu puntuación y el comentario ya publicado para este juego.</p>
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
                    <p class="aviso-resena">Esta reseña ya estaba publicada. Los cambios se guardarán sobre la misma entrada.</p>
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
                                    <span class="estrella-puntuacion" data-estrella="<?= $i ?>" aria-label="<?= $i ?> estrellas">
                                        <i class="fa-regular fa-star"></i>
                                    </span>
                                <?php endfor; ?>
                            </div>
                            <div class="fila-puntuacion">
                                <p class="texto-puntuacion" id="texto-puntuacion">Sin puntuar</p>
                                <button type="button" class="limpiar-puntuacion" id="limpiar-puntuacion"<?= $puntuacion === '' ? ' hidden' : '' ?>>Quitar</button>
                            </div>
                            <input type="hidden" id="puntuacion" name="puntuacion" value="<?= htmlspecialchars($puntuacion) ?>">
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo campo-comentario">
                            <label for="comentario">Tu comentario</label>
                            <textarea id="comentario" name="comentario" rows="10" maxlength="2000" placeholder="Cuéntanos qué te ha parecido este juego, qué destaca y qué te ha dejado peor sabor de boca si es el caso."><?= htmlspecialchars($comentario) ?></textarea>
                            <div class="meta-comentario">
                                <span class="ayuda-comentario">Entre 20 y 2000 caracteres</span>
                                <span class="contador-comentario" id="contador-comentario">0/2000</span>
                            </div>
                            <span class="msg-error"></span>
                        </div>
                    </div>

                    <div class="acciones-formulario">
                        <button type="submit">Guardar cambios</button>
                        <a class="boton-secundario" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Cancelar</a>
                    </div>
                </form>

                <form method="POST" class="form-eliminar-resena">
                    <input type="hidden" name="id" value="<?= (int) $juego['igdb_id'] ?>">
                    <input type="hidden" name="accion" value="eliminar_resena">
                    <button type="button" class="abrir-modal-eliminar-resena">Eliminar reseña</button>
                </form>
            </section>
        </div>
        <div class="modal-quitar-biblioteca" id="modalEliminarResena" hidden>
            <div class="modal-quitar-fondo"></div>
            <div class="modal-quitar-panel" role="dialog" aria-modal="true" aria-labelledby="tituloEliminarResena">
                <h2 id="tituloEliminarResena">Eliminar reseña</h2>
                <p>La reseña dejará de aparecer en la ficha del juego y en tu perfil.</p>
                <div class="acciones-modal-quitar">
                    <button type="button" class="boton-cancelar-quitar">Cancelar</button>
                    <button type="button" class="boton-confirmar-quitar">Eliminar reseña</button>
                </div>
            </div>
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
