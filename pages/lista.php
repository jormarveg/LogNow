<?php
require '../includes/auth.php';
require '../includes/listas_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$idUsuario = (int) getUsuario()['id'];
$idLista = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id_lista'] ?? 0);
$lista = $idLista > 0 ? listaUsuario($db, $idUsuario, $idLista) : null;

if (!$lista) {
    http_response_code(404);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lista) {
    $accion = $_POST['accion'] ?? '';
    $idVideojuego = (int) ($_POST['id_videojuego'] ?? 0);

    if ($accion === 'quitar' && $idVideojuego > 0) {
        listaQuitarJuego($db, $idUsuario, $idLista, $idVideojuego);
        header('Location: /lista.php?id=' . $idLista . '&lista=quitado');
        exit;
    }
}

$juegosLista = $lista ? listaJuegos($db, $idUsuario, $idLista) : [];
$mensajeLista = $_GET['lista'] ?? '';

$titulo = $lista ? $lista['nombre'] . ' — LogNow!' : 'Lista no encontrada — LogNow!';
$css = ['listas.css'];
$pagina = 'listas';
require '../includes/header.php';
?>

<main class="container">
    <?php if (!$lista): ?>
        <section class="panel-vacio-listas">
            <h1>Lista no encontrada</h1>
            <p>No hemos encontrado esa lista entre tus listas personales.</p>
            <a class="boton-secundario-listas" href="/perfil.php?tab=listas">Volver a mis listas</a>
        </section>
    <?php else: ?>
        <section class="cabecera-listas">
            <div>
                <p class="eyebrow">Lista personal</p>
                <h1><?= htmlspecialchars($lista['nombre']) ?></h1>
                <p class="texto-cabecera">
                    <?= htmlspecialchars((string) ($lista['descripcion'] ?: 'Colección personal de juegos guardados en LogNow!.')) ?>
                </p>
            </div>
            <div class="acciones-cabecera-lista">
                <a class="boton-secundario-listas" href="/perfil.php?tab=listas">Mis listas</a>
                <a class="boton-principal-listas" href="/crear-lista.php?id=<?= (int) $lista['id'] ?>">Editar</a>
            </div>
        </section>

        <?php if ($mensajeLista === 'creada'): ?>
            <p class="mensaje-listas exito">Lista creada correctamente.</p>
        <?php elseif ($mensajeLista === 'actualizada'): ?>
            <p class="mensaje-listas exito">Lista actualizada correctamente.</p>
        <?php elseif ($mensajeLista === 'quitado'): ?>
            <p class="mensaje-listas exito">Juego quitado de la lista.</p>
        <?php endif; ?>

        <section class="resumen-lista">
            <article>
                <span><?= listaTotalJuegosTexto($lista['total_juegos']) ?></span>
                <p>Guardados en esta lista</p>
            </article>
            <article>
                <span><?= listaFechaBonita($lista['fecha_creacion']) ?></span>
                <p>Fecha de creación</p>
            </article>
        </section>

        <?php if ($juegosLista): ?>
            <section class="grid-juegos-lista">
                <?php foreach ($juegosLista as $juegoLista): ?>
                    <article class="tarjeta-juego-lista">
                        <a class="portada-juego-lista" href="/juego.php?id=<?= (int) $juegoLista['igdb_id'] ?>">
                            <img src="<?= htmlspecialchars(urlPortadaJuego($juegoLista['portada_url'] ?? '', $juegoLista['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juegoLista['titulo']) ?>">
                        </a>
                        <div class="contenido-juego-lista">
                            <h2><a href="/juego.php?id=<?= (int) $juegoLista['igdb_id'] ?>"><?= htmlspecialchars($juegoLista['titulo']) ?></a></h2>
                            <p>
                                <?php if (!empty($juegoLista['fecha_lanzamiento'])): ?>
                                    <?= listaFechaBonita($juegoLista['fecha_lanzamiento']) ?>
                                <?php else: ?>
                                    Sin fecha guardada
                                <?php endif; ?>
                            </p>
                            <form method="POST">
                                <input type="hidden" name="accion" value="quitar">
                                <input type="hidden" name="id_lista" value="<?= (int) $lista['id'] ?>">
                                <input type="hidden" name="id_videojuego" value="<?= (int) $juegoLista['id'] ?>">
                                <button type="submit">Quitar</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="panel-vacio-listas">
                <h2>Esta lista está vacía</h2>
                <p>Entra en la ficha de un juego y añádelo desde el bloque de listas.</p>
                <a class="boton-secundario-listas" href="/catalogo.php">Ir al catálogo</a>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
