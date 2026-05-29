<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/helpers.php';
require '../includes/biblioteca_helpers.php';
require '../includes/perfil_helpers.php';
require '../includes/listas_helpers.php';

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
    && ($_POST['accion'] ?? '') === 'anadir_lista'
) {
    $idLista = (int) ($_POST['id_lista'] ?? 0);
    $resultadoLista = $idLista > 0 ? listaAnadirJuego($db, $idUsuario, $idLista, (int) $juego['id']) : 'error';

    header('Location: /juego.php?id=' . $idIgdb . '&lista=' . $resultadoLista);
    exit;
}

if (
    $juego
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && esAdmin()
    && ($_POST['accion'] ?? '') === 'eliminar_resena_admin'
) {
    $idResena = (int) ($_POST['id_resena'] ?? 0);
    $destino = '/juego.php?id=' . $idIgdb;
    $stmtResena = $db->prepare('SELECT id
                                FROM RESENA
                                WHERE id = ? AND id_videojuego = ?
                                LIMIT 1');
    $stmtResena->execute([$idResena, (int) $juego['id']]);

    if ($stmtResena->fetch()) {
        try {
            $db->beginTransaction();

            $stmtReportes = $db->prepare('DELETE FROM REPORTE WHERE id_resena = ?');
            $stmtReportes->execute([$idResena]);

            $stmtBorrar = $db->prepare('DELETE FROM RESENA WHERE id = ?');
            $stmtBorrar->execute([$idResena]);

            $db->commit();
            header('Location: ' . $destino . '&resena=eliminada');
            exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }
    }

    header('Location: ' . $destino . '&resena=error');
    exit;
}

if (
    $juego
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && estaLogueado()
    && ($_POST['accion'] ?? '') !== 'anadir_lista'
) {
    $accion = $_POST['accion'] ?? '';
    $destino = '/juego.php?id=' . $idIgdb;
    $nuevoEstado = $_POST['estado_juego'] ?? '';

    if (isset($_POST['toggle_favorito']) && !empty($juego['usuario_juego'])) {
        $nuevoFavorito = !$juego['usuario_juego']['favorito'];
        $actualizadoFavorito = cacheActualizarFavoritoJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], $nuevoFavorito);

        if (!$actualizadoFavorito && $nuevoFavorito) {
            $destino .= '&favorito=limite';
        }

        header('Location: ' . $destino);
        exit;
    } elseif ($accion === 'puntuar_juego') {
        $puntuacion = trim((string) ($_POST['puntuacion'] ?? ''));

        if ($puntuacion !== '' && (!ctype_digit($puntuacion) || !cachePuntuacionResenaValida((int) $puntuacion))) {
            header('Location: ' . $destino . '&puntuacion=error');
            exit;
        }

        try {
            $db->beginTransaction();

            if ($puntuacion !== '' && empty($juego['usuario_juego'])) {
                $resultadoEstado = cacheGuardarEstadoRapidoBiblioteca($db, $idUsuario, (int) $juego['id'], 'completado');

                if ($resultadoEstado === 'error') {
                    throw new RuntimeException('biblioteca');
                }
            }

            if ($puntuacion !== '' && !cacheGuardarPuntuacionUsuario($db, $idUsuario, (int) $juego['id'], (int) $puntuacion)) {
                throw new RuntimeException('puntuacion');
            }

            if ($puntuacion === '' && !cacheLimpiarPuntuacionUsuario($db, $idUsuario, (int) $juego['id'])) {
                throw new RuntimeException('puntuacion');
            }

            $db->commit();
            header('Location: ' . $destino . '&puntuacion=ok');
            exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            header('Location: ' . $destino . '&puntuacion=error');
            exit;
        }
    } elseif (estadoBibliotecaValido($nuevoEstado)) {
        $resultadoEstado = cacheGuardarEstadoRapidoBiblioteca($db, $idUsuario, (int) $juego['id'], $nuevoEstado);

        if ($resultadoEstado === 'creado') {
            $destino .= '&biblioteca=ok';
        } elseif ($resultadoEstado === 'error') {
            $destino .= '&biblioteca=error';
        }

        header('Location: ' . $destino);
        exit;
    }

    header('Location: ' . $destino);
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

function urlJuego($cambios = []) {
    $params = $_GET;
    unset($params['biblioteca'], $params['resena'], $params['puntuacion'], $params['favorito'], $params['lista']);

    foreach ($cambios as $clave => $valor) {
        if ($valor === null || $valor === '' || $valor === 0) {
            unset($params[$clave]);
        } else {
            $params[$clave] = $valor;
        }
    }

    $query = http_build_query($params);

    return '/juego.php' . ($query ? '?' . $query : '');
}

$estados = estadosBibliotecaFicha();

$background = $juego['background_url'] ?? '/assets/img/profile/banner.webp';
$portada = urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'] ?? 'Sin portada');
$estadoActual = $juego['usuario_juego']['estado'] ?? '';
$favorito = !empty($juego['usuario_juego']['favorito']);
$puntuacionUsuario = $juego['usuario_juego']['puntuacion_usuario'] ?? null;
$plataformaUsuario = $juego['usuario_juego']['plataforma'] ?? '';
$puntuacionFormulario = $puntuacionUsuario !== null ? (string) (int) round(((float) $puntuacionUsuario) * 20) : '';
$puntuacionMedia = $juego['resumen_resenas']['media'] ?? null;
$totalResenas = $juego['resumen_resenas']['total'] ?? 0;
$paginaResenas = isset($_GET['rp']) ? max(1, (int) $_GET['rp']) : 1;
$resenasPorPagina = 4;
$totalResenasTexto = $juego ? cacheContarResenasJuego($db, (int) $juego['id']) : 0;
$totalPaginasResenas = max(1, (int) ceil($totalResenasTexto / $resenasPorPagina));

if ($paginaResenas > $totalPaginasResenas) {
    $paginaResenas = $totalPaginasResenas;
}

if ($juego) {
    $juego['resenas'] = cacheResenasJuego($db, (int) $juego['id'], $resenasPorPagina, ($paginaResenas - 1) * $resenasPorPagina);
}

$histograma = $juego['histograma'] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$maxHistograma = max($histograma);
$mensajeBiblioteca = $_GET['biblioteca'] ?? '';
$mensajeResena = $_GET['resena'] ?? '';
$mensajePuntuacion = $_GET['puntuacion'] ?? '';
$mensajeFavorito = $_GET['favorito'] ?? '';
$mensajeLista = $_GET['lista'] ?? '';
$listasUsuario = estaLogueado() && $juego ? listasUsuario($db, $idUsuario) : [];
$generos = $juego ? implode(' · ', $juego['generos']) : '';
$plataformas = $juego ? implode(' · ', $juego['plataformas']) : '';
$titulo = $juego ? $juego['titulo'] . ' — LogNow!' : 'Juego no encontrado — LogNow!';
$pagina = 'catalogo';
$css = ['resenas.css', 'juego.css'];
$js = ['puntuacion.js', 'juego.js'];
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
            <?php elseif ($mensajeBiblioteca === 'error'): ?>
                <p class="mensaje-juego aviso">No se ha podido actualizar tu biblioteca.</p>
            <?php elseif ($mensajeResena === 'ok'): ?>
                <p class="mensaje-juego exito">Reseña publicada correctamente.</p>
            <?php elseif ($mensajeResena === 'actualizada'): ?>
                <p class="mensaje-juego exito">Reseña actualizada correctamente.</p>
            <?php elseif ($mensajeResena === 'eliminada'): ?>
                <p class="mensaje-juego exito">Reseña eliminada correctamente.</p>
            <?php elseif ($mensajeResena === 'error'): ?>
                <p class="mensaje-juego aviso">No se ha podido eliminar la reseña.</p>
            <?php elseif ($mensajePuntuacion === 'ok'): ?>
                <p class="mensaje-juego exito">Puntuación guardada correctamente.</p>
            <?php elseif ($mensajePuntuacion === 'error'): ?>
                <p class="mensaje-juego aviso">No se ha podido guardar la puntuación.</p>
            <?php elseif ($mensajeBiblioteca === 'quitado'): ?>
                <p class="mensaje-juego exito">Juego quitado de tu biblioteca.</p>
            <?php elseif ($mensajeFavorito === 'limite'): ?>
                <p class="mensaje-juego aviso">Has alcanzado el límite de juegos favoritos.</p>
            <?php elseif ($mensajeLista === 'ok'): ?>
                <p class="mensaje-juego exito">Juego añadido correctamente a la lista.</p>
            <?php elseif ($mensajeLista === 'existe'): ?>
                <p class="mensaje-juego aviso">Ese juego ya estaba guardado en esa lista.</p>
            <?php elseif ($mensajeLista === 'error'): ?>
                <p class="mensaje-juego aviso">No se ha podido añadir el juego a la lista.</p>
            <?php endif; ?>

            <aside class="sidebar">
                <nav class="estados-juego">
                    <?php foreach ($estados as $clave => $estado): ?>
                        <?php if (estaLogueado()): ?>
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
                            <?php if ($puntuacionUsuario !== null): ?>
                                <p class="numero-puntuacion"><?= puntuacionVisible($puntuacionUsuario, '') ?></p>
                            <?php endif; ?>
                            <form class="form-puntuacion-juego" method="POST">
                                <input type="hidden" name="accion" value="puntuar_juego">
                                <input type="hidden" name="id_videojuego" value="<?= (int) $juego['id'] ?>">
                                <div class="selector-puntuacion" id="selector-puntuacion">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="estrella-puntuacion" data-estrella="<?= $i ?>" aria-label="<?= $i ?> estrellas">
                                            <i class="fa-regular fa-star"></i>
                                        </span>
                                    <?php endfor; ?>
                                </div>
                                <div class="fila-puntuacion">
                                    <p class="texto-puntuacion" id="texto-puntuacion">Sin puntuar</p>
                                    <button type="button" class="limpiar-puntuacion" id="limpiar-puntuacion"<?= $puntuacionFormulario === '' ? ' hidden' : '' ?>>Quitar</button>
                                </div>
                                <input type="hidden" id="puntuacion" name="puntuacion" value="<?= htmlspecialchars($puntuacionFormulario) ?>">
                                <p class="mensaje-puntuacion-juego" aria-live="polite"></p>
                            </form>
                            <?php if ($puntuacionUsuario !== null && $plataformaUsuario): ?>
                                <p class="nota-usuario">
                                    En <?= htmlspecialchars($plataformaUsuario) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!$estadoActual): ?>
                                <a class="cta-biblioteca" href="/registrar-juego.php?id=<?= (int) $juego['igdb_id'] ?>">Añadir a biblioteca</a>
                            <?php else: ?>
                                <a class="cta-biblioteca secundaria" href="/registrar-juego.php?id=<?= (int) $juego['igdb_id'] ?>&editar=1">Editar</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="nota-usuario">Inicia sesión para guardar tu estado y tu puntuación</p>
                            <a class="cta-biblioteca secundaria" href="/login.php">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>

                    <div class="media-juego">
                        <h3>Puntuación media</h3>
                        <?php if ($puntuacionMedia !== null): ?>
                            <p class="numero-puntuacion"><?= puntuacionVisible($puntuacionMedia, '') ?></p>
                            <div class="grafica">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php $altura = $maxHistograma > 0 ? max(10, (int) round(($histograma[$i] / $maxHistograma) * 100)) : 0; ?>
                                    <div class="barra"<?= $altura > 0 ? ' style="height: ' . $altura . '%"' : '' ?>></div>
                                <?php endfor; ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span><?= $i ?> <i class="fa-solid fa-star"></i></span>
                                <?php endfor; ?>
                            </div>
                            <p class="total-resenas"><?= $totalResenas === 1 ? '1 puntuación' : $totalResenas . ' puntuaciones' ?></p>
                        <?php else: ?>
                            <p class="total-resenas">Sin puntuaciones todavía</p>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if (estaLogueado()): ?>
                    <section class="listas-juego">
                        <h2>Listas</h2>
                        <?php if ($listasUsuario): ?>
                            <p>Guarda este juego en una de tus listas personales.</p>
                            <form method="POST">
                                <input type="hidden" name="accion" value="anadir_lista">
                                <label for="id_lista_juego">Lista<span class="asterisco-obligatorio">*</span></label>
                                <select id="id_lista_juego" name="id_lista" required>
                                    <?php foreach ($listasUsuario as $listaUsuario): ?>
                                        <option value="<?= (int) $listaUsuario['id'] ?>"><?= htmlspecialchars($listaUsuario['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Añadir</button>
                            </form>
                            <a href="/perfil.php?tab=listas">Ver mis listas</a>
                        <?php else: ?>
                            <p>Crea una lista desde tu perfil para guardar este juego.</p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
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

                <section class="resenas-recientes resenas-juego" id="resenas">
                    <div class="cabecera-resenas-juego">
                        <h2>Reseñas</h2>
                        <?php if (estaLogueado() && $estadoActual): ?>
                            <a class="cta-resena-inline" href="/<?= !empty($juego['usuario_juego']['tiene_resena_texto']) ? 'editar-resena' : 'escribir-resena' ?>.php?id=<?= (int) $juego['igdb_id'] ?>">
                                <?= !empty($juego['usuario_juego']['tiene_resena_texto']) ? 'Editar reseña' : 'Escribir reseña' ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($juego['resenas'])): ?>
                        <div class="carousel">
                            <?php foreach ($juego['resenas'] as $resena): ?>
                                <?php
                                $puedeEliminarResena = esAdmin();
                                $puedeReportar = estaLogueado() && !$puedeEliminarResena && (int) $resena['id_usuario'] !== $idUsuario;
                                $comentarioResena = trim((string) $resena['comentario']);
                                $resenaLarga = mb_strlen($comentarioResena, 'UTF-8') > 650;
                                ?>
                                <article class="elemento-carousel mini-resena<?= $resenaLarga ? ' resena-con-leer' : '' ?>">
                                    <div class="mini-portada">
                                        <img src="<?= htmlspecialchars($resena['avatar'] ?: '/assets/img/profile/user.webp') ?>" alt="Avatar de <?= htmlspecialchars($resena['nick']) ?>">
                                    </div>
                                    <div class="nombre-puntuacion">
                                        <h4><a href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>"><?= htmlspecialchars($resena['nick']) ?></a></h4>
                                        <div class="puntuacion">
                                            <i class="fa-solid fa-star"></i>
                                            <span><?= puntuacionVisible($resena['puntuacion_estrellas'], '') ?></span>
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
                                            <div class="meta-resena-linea">
                                                <div class="estrellas"><?= estrellasHtml($resena['puntuacion_estrellas']) ?></div>
                                                <p class="autor-resena">por <strong><a href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>"><?= htmlspecialchars($resena['nick']) ?></a></strong></p>
                                            </div>
                                        </div>
                                        <p class="fecha"><?= fechaBonita($resena['fecha_publicacion'], true) ?></p>
                                    </div>
                                    <?php if ($comentarioResena !== ''): ?>
                                        <p class="texto<?= $resenaLarga ? ' texto-recortado' : '' ?>"><?= nl2br(htmlspecialchars($comentarioResena)) ?></p>
                                        <?php if ($resenaLarga): ?>
                                            <button class="boton-leer-resena" type="button" aria-expanded="false">Leer más</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($puedeEliminarResena): ?>
                                        <form method="POST" class="form-eliminar-resena-admin">
                                            <input type="hidden" name="accion" value="eliminar_resena_admin">
                                            <input type="hidden" name="id_resena" value="<?= (int) $resena['id'] ?>">
                                            <button class="boton-eliminar-resena-admin abrir-modal-eliminar-resena-admin" type="button">Eliminar</button>
                                        </form>
                                    <?php elseif ($puedeReportar): ?>
                                        <button class="boton-reportar-resena" type="button" data-id-resena="<?= (int) $resena['id'] ?>">Reportar</button>
                                    <?php endif; ?>
                                    <p class="username"><a href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>"><?= htmlspecialchars($resena['nick']) ?></a></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($totalPaginasResenas > 1): ?>
                            <nav class="paginacion paginacion-resenas" aria-label="Paginación de reseñas">
                                <?php if ($paginaResenas > 1): ?>
                                    <a href="<?= htmlspecialchars(urlJuego(['rp' => $paginaResenas - 1])) ?>#resenas">Anterior</a>
                                <?php endif; ?>

                                <?php foreach (paginasCompactas($paginaResenas, $totalPaginasResenas) as $item): ?>
                                    <?php if ($item === '...'): ?>
                                        <span class="separador">...</span>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars(urlJuego(['rp' => $item])) ?>#resenas"<?= $item === $paginaResenas ? ' class="active"' : '' ?>><?= $item ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if ($paginaResenas < $totalPaginasResenas): ?>
                                    <a href="<?= htmlspecialchars(urlJuego(['rp' => $paginaResenas + 1])) ?>#resenas">Siguiente</a>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="sin-resenas">
                            <p>Todavía no hay reseñas publicadas para este juego en LogNow!.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </div>
    </main>
    <?php if (estaLogueado()): ?>
        <div class="modal-reporte" id="modalReporte" hidden>
            <div class="modal-reporte-fondo"></div>
            <div class="modal-reporte-panel" role="dialog" aria-modal="true" aria-labelledby="tituloReporte">
                <form id="formReporte">
                    <input type="hidden" name="id_resena" id="idResenaReporte">
                    <h2 id="tituloReporte">Reportar reseña</h2>
                    <p class="texto-modal-reporte">Cuéntanos brevemente por qué quieres reportar este comentario.</p>
                    <label for="motivoReporte">Motivo<span class="asterisco-obligatorio">*</span></label>
                    <textarea id="motivoReporte" name="motivo" minlength="5" maxlength="255" required></textarea>
                    <p class="mensaje-reporte" id="mensajeReporte"></p>
                    <div class="acciones-reporte">
                        <button type="button" class="boton-cancelar-reporte">Cancelar</button>
                        <button type="submit" class="boton-enviar-reporte">Reportar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <?php if (esAdmin()): ?>
        <div class="modal-reporte modal-eliminar-resena-admin" id="modalEliminarResenaAdmin" hidden>
            <div class="modal-reporte-fondo"></div>
            <div class="modal-reporte-panel" role="dialog" aria-modal="true" aria-labelledby="tituloEliminarResenaAdmin">
                <h2 id="tituloEliminarResenaAdmin">Eliminar reseña</h2>
                <p class="texto-modal-reporte">¿Quieres eliminar esta reseña definitivamente?</p>
                <div class="acciones-reporte">
                    <button type="button" class="boton-cancelar-reporte">Cancelar</button>
                    <button type="button" class="boton-enviar-reporte boton-confirmar-eliminar-resena-admin">Eliminar</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
