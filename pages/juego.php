<?php
require '../api/cache.php';

$idIgdb = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$juego = $idIgdb > 0 ? cacheObtenerJuegoPorIgdbId($db, $idIgdb) : null;

if (!$juego) {
    http_response_code(404);
}

$titulo = $juego ? $juego['titulo'] . ' — LogNow!' : 'Juego no encontrado — LogNow!';
$pagina = 'catalogo';
$css = [];
require '../includes/header.php';
?>

<main class="container">
    <?php if ($juego): ?>
        <section class="detalle-juego">
            <h1><?= htmlspecialchars($juego['titulo']) ?></h1>
            <div class="portada-juego" style="max-width: 18rem;">
                <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
            </div>
            <?php if (!empty($juego['fecha_lanzamiento'])): ?>
                <p><strong>Lanzamiento:</strong> <?= htmlspecialchars($juego['fecha_lanzamiento']) ?></p>
            <?php endif; ?>
            <?php if (!empty($juego['descripcion'])): ?>
                <p><?= nl2br(htmlspecialchars($juego['descripcion'])) ?></p>
            <?php else: ?>
                <p>Este juego ya existe en el catálogo de LogNow!. La ficha completa se terminará más adelante.</p>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="detalle-juego">
            <h1>Juego no encontrado</h1>
            <p>Este juego todavía no está disponible en el catálogo local de LogNow!.</p>
        </section>
    <?php endif; ?>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
