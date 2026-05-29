<?php
require '../includes/auth.php';
require '../includes/listas_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$idUsuario = (int) getUsuario()['id'];
$idLista = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id_lista'] ?? 0);
$modoEdicion = $idLista > 0;
$lista = $modoEdicion ? listaUsuario($db, $idUsuario, $idLista) : null;

if ($modoEdicion && !$lista) {
    http_response_code(404);
}

$nombre = $lista['nombre'] ?? '';
$descripcion = $lista['descripcion'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$modoEdicion || $lista)) {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

    if ($nombre === '') {
        $error = 'Escribe un nombre para la lista';
    } elseif (mb_strlen($nombre, 'UTF-8') > 100) {
        $error = 'El nombre no puede superar los 100 caracteres';
    } elseif (mb_strlen($descripcion, 'UTF-8') > 255) {
        $error = 'La descripción no puede superar los 255 caracteres';
    } else {
        if ($modoEdicion) {
            listaActualizar($db, $idUsuario, $idLista, $nombre, $descripcion !== '' ? $descripcion : null);
            header('Location: /lista.php?id=' . $idLista . '&lista=actualizada');
            exit;
        }

        $nuevoId = listaCrear($db, $idUsuario, $nombre, $descripcion !== '' ? $descripcion : null);
        header('Location: /lista.php?id=' . $nuevoId . '&lista=creada');
        exit;
    }
}

$titulo = $modoEdicion && !$lista ? 'Lista no encontrada — LogNow!' : ($modoEdicion ? 'Editar lista — LogNow!' : 'Crear lista — LogNow!');
$css = ['listas.css'];
$pagina = 'listas';
require '../includes/header.php';
?>

<main class="container">
    <?php if ($modoEdicion && !$lista): ?>
        <section class="panel-vacio-listas">
            <h1>Lista no encontrada</h1>
            <p>No hemos encontrado esa lista entre tus listas personales.</p>
            <a class="boton-secundario-listas" href="/perfil.php?tab=listas">Volver a mis listas</a>
        </section>
    <?php else: ?>
        <section class="cabecera-listas">
            <div>
                <p class="eyebrow">Listas personales</p>
                <h1><?= $modoEdicion ? 'Editar lista' : 'Crear lista' ?></h1>
                <p class="texto-cabecera">
                    <?= $modoEdicion ? 'Actualiza el nombre y la descripción de esta colección.' : 'Crea una colección sencilla y luego añade juegos desde sus fichas.' ?>
                </p>
            </div>
            <a class="boton-secundario-listas" href="<?= $modoEdicion ? '/lista.php?id=' . (int) $idLista : '/perfil.php?tab=listas' ?>">Volver</a>
        </section>

        <section class="formulario-lista">
            <?php if ($error): ?>
                <p class="mensaje-listas error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" novalidate>
                <?php if ($modoEdicion): ?>
                    <input type="hidden" name="id_lista" value="<?= (int) $idLista ?>">
                <?php endif; ?>

                <div class="campo-lista">
                    <label for="nombre">Nombre<span class="asterisco-obligatorio">*</span></label>
                    <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($nombre) ?>">
                </div>

                <div class="campo-lista">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" maxlength="255"><?= htmlspecialchars((string) $descripcion) ?></textarea>
                </div>

                <div class="acciones-formulario-lista">
                    <button type="submit"><?= $modoEdicion ? 'Guardar cambios' : 'Crear lista' ?></button>
                    <a href="<?= $modoEdicion ? '/lista.php?id=' . (int) $idLista : '/perfil.php?tab=listas' ?>">Cancelar</a>
                </div>
            </form>
        </section>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
