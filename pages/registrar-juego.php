<?php
require '../api/cache.php';
require '../includes/auth.php';

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

$estados = [
    'jugando' => 'Jugando',
    'completado' => 'Completado',
    'pendiente' => 'Pendiente',
    'abandonado' => 'Abandonado'
];

$idIgdb = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);
$idUsuario = (int) getUsuario()['id'];
$juego = $idIgdb > 0 ? cacheDetalleJuego($db, $idIgdb, $idUsuario) : null;

if (!$juego) {
    http_response_code(404);
}

if ($juego && !empty($juego['usuario_juego'])) {
    header('Location: /juego.php?id=' . $idIgdb . '&biblioteca=existe');
    exit;
}

$error = '';
$estado = 'pendiente';
$idPlataforma = 0;
$horasJugadas = '0';
$minutosJugados = '0';
$fechaInicio = '';
$fechaFin = '';
$favorito = false;
$plataformas = $juego['plataformas_detalle'] ?? [];
$plataformasValidas = array_map(static fn($plataforma) => (int) $plataforma['id'], $plataformas);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $juego) {
    $estado = $_POST['estado'] ?? 'pendiente';
    $idPlataforma = (int) ($_POST['plataforma'] ?? 0);
    $horasJugadas = trim((string) ($_POST['horas_jugadas'] ?? '0'));
    $minutosJugados = trim((string) ($_POST['minutos_jugados'] ?? '0'));
    $fechaInicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
    $fechaFin = trim((string) ($_POST['fecha_fin'] ?? ''));
    $favorito = isset($_POST['favorito']);

    if (!isset($estados[$estado])) {
        $error = 'Selecciona un estado válido';
    } elseif ($idPlataforma <= 0 || !in_array($idPlataforma, $plataformasValidas, true)) {
        $error = 'Selecciona una plataforma válida para este juego';
    } elseif ($horasJugadas === '' || !ctype_digit($horasJugadas)) {
        $error = 'Las horas jugadas deben ser un número válido';
    } elseif ($minutosJugados === '' || !ctype_digit($minutosJugados)) {
        $error = 'Los minutos jugados deben ser un número válido';
    } elseif ((int) $minutosJugados > 59) {
        $error = 'Los minutos deben estar entre 0 y 59';
    } elseif (!fechaBibliotecaValida($fechaInicio)) {
        $error = 'La fecha de inicio no es válida';
    } elseif (!fechaBibliotecaValida($fechaFin)) {
        $error = 'La fecha de fin no es válida';
    } else {
        if ($estado !== 'completado') {
            $fechaFin = '';
        }

        if ($fechaInicio !== '' && $fechaFin !== '' && strtotime($fechaFin) < strtotime($fechaInicio)) {
            $error = 'La fecha de fin no puede ser anterior a la de inicio';
        } else {
            try {
                cacheGuardarJuegoBiblioteca($db, $idUsuario, (int) $juego['id'], [
                    'id_plataforma' => $idPlataforma,
                    'estado' => $estado,
                    'horas_jugadas' => (int) $horasJugadas,
                    'minutos_jugados' => (int) $minutosJugados,
                    'fecha_inicio' => $fechaInicio !== '' ? $fechaInicio : null,
                    'fecha_fin' => $fechaFin !== '' ? $fechaFin : null,
                    'favorito' => $favorito
                ]);

                header('Location: /juego.php?id=' . $idIgdb . '&biblioteca=ok');
                exit;
            } catch (PDOException $e) {
                $error = $e->getCode() === '23000'
                    ? 'Ese juego ya está en tu biblioteca'
                    : 'No se ha podido guardar el juego ahora mismo';
            }
        }
    }
}

$titulo = $juego ? 'Registrar juego — LogNow!' : 'Juego no encontrado — LogNow!';
$css = ['biblioteca.css'];
$pagina = 'registrar-juego';
$js = ['biblioteca.js'];
require '../includes/header.php';
?>

<main class="container">
    <?php if ($juego): ?>
        <section class="cabecera-biblioteca">
            <div>
                <p class="eyebrow">Biblioteca personal</p>
                <h1>Registrar juego</h1>
                <p class="texto-cabecera">Guarda el estado de este juego en tu biblioteca y deja preparado tu seguimiento.</p>
            </div>
            <a class="boton-secundario" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Volver a la ficha</a>
        </section>

        <div class="bloque-biblioteca">
            <section class="resumen-juego-biblioteca">
                <div class="portada-resumen">
                    <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
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
                            <select id="plataforma" name="plataforma" required>
                                <option value="0">Selecciona una plataforma</option>
                                <?php foreach ($plataformas as $plataforma): ?>
                                    <option value="<?= (int) $plataforma['id'] ?>"<?= $idPlataforma === (int) $plataforma['id'] ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($plataforma['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo">
                            <label for="horas_jugadas">Horas jugadas</label>
                            <input type="number" id="horas_jugadas" name="horas_jugadas" min="0" step="1" value="<?= htmlspecialchars($horasJugadas) ?>">
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo">
                            <label for="minutos_jugados">Minutos</label>
                            <input type="number" id="minutos_jugados" name="minutos_jugados" min="0" max="59" step="1" value="<?= htmlspecialchars($minutosJugados) ?>">
                            <span class="msg-error"></span>
                        </div>

                        <div class="campo">
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
                        <button type="submit">Guardar en mi biblioteca</button>
                        <a class="boton-secundario" href="/perfil.php?tab=juegos">Ver mi biblioteca</a>
                    </div>
                </form>
            </section>
        </div>
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
