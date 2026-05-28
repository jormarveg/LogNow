<?php
require '../includes/auth.php';

$titulo = 'Acerca de — LogNow!';
$css = ['legal.css'];
$pagina = 'acerca';
require '../includes/header.php';
?>

<main class="container">
    <section class="pagina-legal">
        <h1>Acerca de LogNow!</h1>
        <div class="cuerpo-texto">
            <p>LogNow! es una aplicación web de seguimiento y reseñas de videojuegos. Permite consultar juegos, guardar una biblioteca personal, puntuar títulos, escribir reseñas, crear listas y ver perfiles de otros usuarios.</p>

            <section>
                <h2>Objetivo del proyecto</h2>
                <p>El objetivo principal es centralizar la actividad de un jugador en torno a sus videojuegos. Desde una cuenta, el usuario puede registrar qué juegos está jugando, cuáles ha completado, cuáles tiene pendientes y cuáles ha abandonado.</p>
                <p>También ayuda a conservar opiniones personales mediante puntuaciones, favoritos y reseñas asociadas a cada juego.</p>
            </section>

            <section>
                <h2>Información de videojuegos</h2>
                <p>Los datos generales de los juegos se obtienen desde IGDB, una base de datos externa especializada en videojuegos.</p>
                <p>LogNow! guarda una copia local de esos datos para que el catálogo pueda funcionar con información propia y no dependa de consultar la API en cada carga de página.</p>
            </section>

            <section>
                <h2>Usuarios de la aplicación</h2>
                <p>Los invitados pueden navegar por el catálogo, buscar juegos, leer reseñas y consultar perfiles públicos.</p>
                <p>Los usuarios registrados pueden gestionar su biblioteca, publicar reseñas, puntuar juegos, crear listas y editar su perfil. Los administradores pueden gestionar usuarios, revisar reportes y realizar la importación inicial del catálogo.</p>
            </section>

            <section>
                <h2>Proyecto académico</h2>
                <p>LogNow! se ha desarrollado por Jorge Martínez Vegara como proyecto final del Ciclo Formativo de Grado Superior de Desarrollo de Aplicaciones Web. 2025-2026.</p>
            </section>
        </div>
    </section>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
