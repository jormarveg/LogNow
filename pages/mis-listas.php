<?php
require '../includes/auth.php';
require '../includes/listas_helpers.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$idUsuario = (int) getUsuario()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idLista = (int) ($_POST['id_lista'] ?? 0);

    if ($accion === 'borrar' && $idLista > 0) {
        listaBorrar($db, $idUsuario, $idLista);
        header('Location: /mis-listas.php?lista=borrada');
        exit;
    }
}

$listas = listasUsuario($db, $idUsuario);
$mensajeLista = $_GET['lista'] ?? '';

$titulo = 'Mis listas — LogNow!';
$css = ['listas.css'];
$pagina = 'listas';
require '../includes/header.php';
?>

<main class="container">
    <section class="cabecera-listas">
        <div>
            <p class="eyebrow">Listas personales</p>
            <h1>Mis listas</h1>
            <p class="texto-cabecera">Organiza tus juegos en colecciones sencillas para tenerlos a mano.</p>
        </div>
        <a class="boton-principal-listas" href="/crear-lista.php">Crear lista</a>
    </section>

    <?php if ($mensajeLista === 'borrada'): ?>
        <p class="mensaje-listas exito">Lista borrada correctamente.</p>
    <?php endif; ?>

    <?php if ($listas): ?>
        <section class="grid-listas">
            <?php foreach ($listas as $lista): ?>
                <article class="tarjeta-lista">
                    <div>
                        <p class="meta-lista"><?= listaFechaBonita($lista['fecha_creacion']) ?></p>
                        <h2><a href="/lista.php?id=<?= (int) $lista['id'] ?>"><?= htmlspecialchars($lista['nombre']) ?></a></h2>
                        <p class="descripcion-lista">
                            <?= htmlspecialchars((string) ($lista['descripcion'] ?: 'Sin descripción')) ?>
                        </p>
                    </div>

                    <div class="pie-lista">
                        <span><?= listaTotalJuegosTexto($lista['total_juegos']) ?></span>
                        <div class="acciones-lista">
                            <a href="/lista.php?id=<?= (int) $lista['id'] ?>">Ver</a>
                            <a href="/crear-lista.php?id=<?= (int) $lista['id'] ?>">Editar</a>
                            <form method="POST">
                                <input type="hidden" name="accion" value="borrar">
                                <input type="hidden" name="id_lista" value="<?= (int) $lista['id'] ?>">
                                <button type="submit">Borrar</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="panel-vacio-listas">
            <h2>Todavía no tienes listas</h2>
            <p>Crea tu primera lista.</p>
            <a class="boton-secundario-listas" href="/crear-lista.php">Crear una lista</a>
        </section>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
