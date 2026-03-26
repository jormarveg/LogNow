<?php
$titulo = 'Perfil — LogNow!';
$css = ['resenas.css', 'perfil.css'];
$pagina = 'perfil';
require '../includes/header.php';
?>

<section class="encabezado-perfil">
    <div class="container">
        <div class="foto-perfil">
            <img src="/assets/img/profile/user.webp" alt="Foto de perfil">
        </div>
        <h1 class="nombre">Jorge Martínez</h1>
    </div>
</section>

<nav class="profile-tabs">
    <div class="container">
        <ul>
            <li class="active"><a href="#"><i class="fa-solid fa-user"></i>Perfil</a></li>
            <li><a href="#"><i class="fa-solid fa-gamepad"></i>Tus juegos</a></li>
            <li><a href="#"><i class="fa-solid fa-message"></i>Tus reseñas</a></li>
            <a class="editar" href="#">Editar perfil</a>
        </ul>
    </div>
</nav>

<main class="container">
    <div class="content-grid">
        <aside class="sidebar">
            <section class="bio">
                <h2>Bio</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusamus alias consectetur cum
                    doloribus nesciunt, nobis officia pariatur porro totam.</p>
            </section>

            <section class="stats">
                <div class="jugados">
                    <h3>Jugados</h3>
                    <span class="datos">82</span>
                </div>
                <div class="este-ano">
                    <h3>Este año</h3>
                    <span class="datos">4</span>
                </div>
            </section>

            <section class="puntuaciones">
                <h3>Tus puntuaciones</h3>
                <div class="grafica">
                    <div class="barra barra1"></div>
                    <div class="barra barra2"></div>
                    <div class="barra barra3"></div>
                    <div class="barra barra4"></div>
                    <div class="barra barra5"></div>
                    <span>1 <i class="fa-solid fa-star"></i></span>
                    <span>2 <i class="fa-solid fa-star"></i></span>
                    <span>3 <i class="fa-solid fa-star"></i></span>
                    <span>4 <i class="fa-solid fa-star"></i></span>
                    <span>5 <i class="fa-solid fa-star"></i></span>
                </div>
            </section>
        </aside>
        <hr class="separador">
        <section class="principal">
            <section class="favoritos">
                <h2>Tus favoritos</h2>
                <div class="carousel">
                    <div class="favorito">
                        <div class="portada"><img src="/assets/img/covers/expedition33.jpg"></div>
                        <p>Juego 1</p>
                    </div>
                    <div class="favorito elemento-carousel">
                        <div class="portada"><img src="/assets/img/covers/expedition33.jpg"></div>
                        <p>Juego 2</p>
                    </div>
                    <div class="favorito elemento-carousel">
                        <div class="portada"><img src="/assets/img/covers/expedition33.jpg"></div>
                        <p>Juego 3</p>
                    </div>
                    <div class="favorito elemento-carousel">
                        <div class="portada"><img src="/assets/img/covers/expedition33.jpg"></div>
                        <p>Juego 4</p>
                    </div>
                </div>
            </section>

            <section class="resenas-recientes">
                <h2>Tus reseñas recientes</h2>
                <div class="carousel">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="elemento-carousel mini-resena">
                        <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                        <div class="nombre-puntuacion">
                            <h4>Juego 1</h4>
                            <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                        </div>
                        <div class="puntuacion-tablet">
                            <div class="titulo-puntuacion-wrapper">
                                <p class="titulo-plataforma"><strong>Juego 1</strong> en <strong>Nintendo Switch</strong></p>
                                <div class="estrellas">
                                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                        class="fa-solid fa-star"></i><i
                                        class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                                </div>
                            </div>
                            <p class="fecha">22 febrero 2026</p>
                        </div>
                        <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt
                            tenetur? Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                        </p>
                        <p class="username">Usuario1</p>
                    </div>
                    <?php endfor; ?>
                </div>
            </section>
        </section>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
