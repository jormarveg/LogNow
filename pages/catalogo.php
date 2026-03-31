<?php
$titulo = 'Catálogo — LogNow!';
$css = ['catalogo.css'];
$pagina = 'catalogo';
require '../includes/header.php';
?>

<main class="container">
    <h1>Todos los juegos</h1>
    <section class="encabezado">
        <p>50.000 juegos</p>
        <p><i class="fa-solid fa-filter"></i> <b>Filtros</b></p>
        <p>Ordenar por </p>
        <p><b><i class="fa-solid fa-arrow-down-wide-short"></i> Puntuación</b></p>
    </section>
    <section class="juegos">
        <?php for ($i = 0; $i < 24; $i++): ?>
        <div class="juego">
            <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
            <div class="titulo"><p>Juego</p></div>
            <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.3</span></div>
        </div>
        <?php endfor; ?>
    </section>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
