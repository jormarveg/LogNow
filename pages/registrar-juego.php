<?php
require '../api/cache.php';
require '../includes/auth.php';
require '../includes/biblioteca_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

function fechaBibliotecaValida($fecha) {
    if ($fecha === '') {
        return true;
    }

    $objeto = DateTime::createFromFormat('Y-m-d', $fecha);

    return $objeto && $objeto->format('Y-m-d') === $fecha;
}

$estados = estadosBiblioteca();

$idIgdb = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);
$idUsuario = (int) getUsuario()['id'];
$juego = $idIgdb > 0 ? cacheDetalleJuego($db, $idIgdb, $idUsuario) : null;
$modoEdicion = isset($_GET['editar']) || isset($_POST['editar']);

if (!$juego) {
    http_response_code(404);
}

if ($juego && !empty($juego['usuario_juego']) && !$modoEdicion) {
    header('Location: /juego.php?id=' . $idIgdb . '&biblioteca=existe');
    exit;
}

$error = '';
$estado = 'pendiente';
$idPlataforma = 0;
$puntuacion = '';
$horasJugadas = '0';
$minutosJugados = '0';
$fechaInicio = '';
$fechaFin = '';
$favorito = false;
$plataformas = $juego['plataformas_detalle'] ?? [];
$plataformasValidas = array_map(static fn($plataforma) => (int) $plataforma['id'], $plataformas);

if ($juego && !empty($juego['usuario_juego']) && $modoEdicion && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $estado = $juego['usuario_juego']['estado'] ?? 'pendiente';
    $idPlataforma = (int) ($juego['usuario_juego']['id_plataforma'] ?? 0);
    $puntuacionGuardada = $juego['usuario_juego']['puntuacion_usuario'] ?? null;
    $puntuacion = $puntuacionGuardada !== null ? (string) (int) round(((float) $puntuacionGuardada) * 20) : '';
    $horasJugadas = (string) (int) ($juego['usuario_juego']['horas_jugadas'] ?? 0);
    $minutosJugados = (string) (int) ($juego['usuario_juego']['minutos_jugados'] ?? 0);
    $fechaInicio = (string) ($juego['usuario_juego']['fecha_inicio'] ?? '');
    $fechaFin = (string) ($juego['usuario_juego']['fecha_fin'] ?? '');
    $favorito = !empty($juego['usuario_juego']['favorito']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $juego && ($_POST['accion'] ?? '') === 'quitar_biblioteca') {
    if (!$modoEdicion || empty($juego['usuario_juego'])) {
        $error = 'Ese juego no está en tu biblioteca';
    } elseif (cacheQuitarJuegoBiblioteca($db, $idUsuario, (int) $juego['id'])) {
        header('Location: /juego.php?id=' . $idIgdb . '&biblioteca=quitado');
        exit;
    } else {
        $error = 'No se ha podido quitar el juego de tu biblioteca';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $juego && ($_POST['accion'] ?? '') !== 'quitar_biblioteca') {
    $estado = $_POST['estado'] ?? 'pendiente';
    $idPlataforma = (int) ($_POST['plataforma'] ?? 0);
    $puntuacion = trim((string) ($_POST['puntuacion'] ?? ''));
    $horasJugadas = trim((string) ($_POST['horas_jugadas'] ?? '0'));
    $minutosJugados = trim((string) ($_POST['minutos_jugados'] ?? '0'));
    $fechaInicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
    $fechaFin = trim((string) ($_POST['fecha_fin'] ?? ''));
    $favorito = isset($_POST['favorito']);

    if (!estadoBibliotecaValido($estado)) {
        $error = 'Selecciona un estado válido';
    } elseif ($idPlataforma > 0 && !in_array($idPlataforma, $plataformasValidas, true)) {
        $error = 'Selecciona una plataforma válida para este juego';
    } elseif ($puntuacion !== '' && (!ctype_digit($puntuacion) || !cachePuntuacionResenaValida((int) $puntuacion))) {
        $error = 'Selecciona una puntuación válida';
    } elseif ($horasJugadas === '' || !ctype_digit($horasJugadas)) {
        $error = 'Las horas jugadas deben ser un número válido';
    } elseif ($minutosJugados === '' || !ctype_digit($minutosJugados)) {
        $error = 'Los minutos jugados deben ser un número válido';
    } elseif ((int) $minutosJugados > 59) {
        $error = 'Los minutos deben estar entre 0 y 59';
    } elseif ($fechaInicio !== '' && !fechaBibliotecaValida($fechaInicio)) {
        $error = 'La fecha de inicio no es válida';
    } elseif ($fechaFin !== '' && !fechaBibliotecaValida($fechaFin)) {
        $error = 'La fecha de fin no es válida';
    } elseif ($favorito && !cachePuedeMarcarFavorito($db, $idUsuario, (int) $juego['id'])) {
        $error = 'Has alcanzado el límite de juegos favoritos';
    } else {
        if ($fechaInicio !== '' && $fechaFin !== '' && strtotime($fechaFin) < strtotime($fechaInicio)) {
            $error = 'La fecha de fin no puede ser anterior a la de inicio';
        } else {
            try {
                $db->beginTransaction();

                $datosBiblioteca = [
                    'id_plataforma' => $idPlataforma,
                    'estado' => $estado,
                    'horas_jugadas' => (int) $horasJugadas,
                    'minutos_jugados' => (int) $minutosJugados,
                    'fecha_inicio' => $fechaInicio !== '' ? $fechaInicio : null,
                    'fecha_fin' => $fechaFin !== '' ? $fechaFin : null,
                    'favorito' => $favorito
                ];

                if ($modoEdicion) {
                    $guardadoBiblioteca = cacheActualizarJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], $datosBiblioteca);
                } else {
                    $guardadoBiblioteca = cacheGuardarJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], $datosBiblioteca);
                }

                if (!$guardadoBiblioteca) {
                    throw new RuntimeException('limite_favoritos');
                }

                if ($puntuacion !== '') {
                    cacheGuardarPuntuacionUsuario($db, $idUsuario, (int) $juego['id'], (int) $puntuacion);
                } elseif ($modoEdicion && !cacheLimpiarPuntuacionUsuario($db, $idUsuario, (int) $juego['id'])) {
                    throw new RuntimeException('resena_publicada');
                }

                $db->commit();
                header('Location: /juego.php?id=' . $idIgdb . '&biblioteca=' . ($modoEdicion ? 'editado' : 'ok'));
                exit;
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                if ($e instanceof RuntimeException && $e->getMessage() === 'limite_favoritos') {
                    $error = 'Has alcanzado el límite de juegos favoritos';
                } elseif ($e instanceof RuntimeException && $e->getMessage() === 'resena_publicada') {
                    $error = 'No puedes quitar la puntuación de una reseña publicada';
                } else {
                    $error = $e->getCode() === '23000'
                        ? 'Ese juego ya está en tu biblioteca'
                        : 'No se ha podido guardar el juego ahora mismo';
                }
            }
        }
    }
}

$titulo = $juego ? (($modoEdicion ? 'Editar juego' : 'Registrar juego') . ' — LogNow!') : 'Juego no encontrado — LogNow!';
$css = ['biblioteca.css'];
$pagina = 'registrar-juego';
$js = ['puntuacion.js', 'biblioteca.js'];
$usarJquery = true;
require '../includes/header.php';
?>

<main class="container">
    <?php if ($juego): ?>
        <section class="cabecera-biblioteca">
            <div>
                <p class="eyebrow">Biblioteca personal</p>
                <h1><?= $modoEdicion ? 'Editar juego' : 'Registrar juego' ?></h1>
                <p class="texto-cabecera">
                    <?= $modoEdicion ? 'Actualiza tu seguimiento de este juego y ajusta los datos de tu biblioteca.' : 'Guarda el estado de este juego en tu biblioteca.' ?>
                </p>
            </div>
            <a class="boton-secundario" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Volver a la ficha</a>
        </section>

        <div class="bloque-biblioteca">
            <section class="resumen-juego-biblioteca">
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
                    <p class="meta-resumen">
                        <?= !empty($juego['plataformas']) ? htmlspecialchars(implode(' · ', $juego['plataformas'])) : 'Sin plataformas guardadas' ?>
                    </p>
                </div>
            </section>

            <section class="formulario-biblioteca">
                <?php if ($error): ?>
                    <p class="error"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" id="form-biblioteca" novalidate>
                    <input type="hidden" name="id" value="<?= (int) $juego['igdb_id'] ?>">
                    <?php if ($modoEdicion): ?>
                        <input type="hidden" name="editar" value="1">
                    <?php endif; ?>

                    <div class="grid-formulario">
                        <div class="campo">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" required>
                                <?php foreach ($estados as $clave => $texto): ?>
                                    <option value="<?= $clave ?>"<?= $estado === $clave ? ' selected' : '' ?>><?= $texto ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo">
                            <label for="plataforma">Plataforma</label>
                            <select id="plataforma" name="plataforma">
                                <option value="0">Sin especificar</option>
                                <?php foreach ($plataformas as $plataforma): ?>
                                    <option value="<?= (int) $plataforma['id'] ?>"<?= $idPlataforma === (int) $plataforma['id'] ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($plataforma['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo campo-puntuacion">
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

                        <div class="bloque-tiempo">
                            <span class="label-tiempo">Tiempo jugado</span>
                            <div class="grupo-tiempo">
                                <div class="campo">
                                    <label for="horas_jugadas">Horas</label>
                                    <input type="number" id="horas_jugadas" name="horas_jugadas" min="0" step="1" value="<?= htmlspecialchars($horasJugadas) ?>">
                                    <span class="msg-error"></span>
                                </div>

                                <div class="campo">
                                    <label for="minutos_jugados">Minutos</label>
                                    <input type="number" id="minutos_jugados" name="minutos_jugados" min="0" max="59" step="1" value="<?= htmlspecialchars($minutosJugados) ?>">
                                    <span class="msg-error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="campo campo-fecha-inicio<?= $estado === 'pendiente' ? ' oculto' : '' ?>">
                            <label for="fecha_inicio">Fecha de inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo campo-fecha-fin<?= $estado === 'completado' ? '' : ' oculto' ?>">
                            <label for="fecha_fin">Fecha de fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
                            <span class="msg-error"></span>
                        </div>
                    </div>

                    <label class="check-favorito" for="favorito">
                        <input type="checkbox" id="favorito" name="favorito"<?= $favorito ? ' checked' : '' ?>>
                        <span>Marcar como favorito</span>
                    </label>

                    <div class="acciones-formulario">
                        <button type="submit"><?= $modoEdicion ? 'Guardar cambios' : 'Guardar en mi biblioteca' ?></button>
                        <a class="boton-secundario" href="/perfil.php?tab=juegos">Ver mi biblioteca</a>
                    </div>
                </form>
                <?php if ($modoEdicion): ?>
                    <form method="POST" class="form-quitar-biblioteca" id="form-quitar-biblioteca">
                        <input type="hidden" name="id" value="<?= (int) $juego['igdb_id'] ?>">
                        <input type="hidden" name="editar" value="1">
                        <input type="hidden" name="accion" value="quitar_biblioteca">
                        <button type="button" class="abrir-modal-quitar">Quitar de mi biblioteca</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
        <?php if ($modoEdicion): ?>
            <div class="modal-quitar-biblioteca" id="modalQuitarBiblioteca" hidden>
                <div class="modal-quitar-fondo"></div>
                <div class="modal-quitar-panel" role="dialog" aria-modal="true" aria-labelledby="tituloQuitarBiblioteca">
                    <h2 id="tituloQuitarBiblioteca">Quitar de mi biblioteca</h2>
                    <p>Se eliminarán tu estado, tu puntuación y tu reseña de este juego.</p>
                    <div class="acciones-modal-quitar">
                        <button type="button" class="boton-cancelar-quitar">Cancelar</button>
                        <button type="button" class="boton-confirmar-quitar">Quitar de mi biblioteca</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <section class="panel-vacio">
            <h1>Juego no encontrado</h1>
            <p>Este juego no está disponible ahora mismo en el catálogo local.</p>
            <a class="boton-secundario" href="/catalogo.php">Volver al catálogo</a>
        </section>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
