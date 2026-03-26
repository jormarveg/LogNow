<?php
$titulo = 'Inicio — LogNow!';
$css = ['resenas.css', 'index.css'];
$pagina = 'inicio';
require 'includes/header.php';
?>

<main class="container">
    <section class="tendencias">
        <h2 class="titulo-mobile">Tendencias</h2>
        <h2 class="titulo-tablet">Juegos en tendencia</h2>
        <div class="carousel-wrapper">
            <button class="flecha prev"><i class="fa-solid fa-angle-left"></i></button>
            <div class="carousel">
                <div class="elemento-carousel">
                    <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.3</span></div>
                    <div class="titulo-puntuacion">
                        <span>Juego 1</span>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                    </div>
                </div>
                <div class="elemento-carousel">
                    <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.6</span></div>
                    <div class="titulo-puntuacion">
                        <span>Juego 2</span>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                    </div>
                </div>
                <div class="elemento-carousel">
                    <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.8</span></div>
                    <div class="titulo-puntuacion">
                        <span>Juego 3</span>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                    </div>
                </div>
                <div class="elemento-carousel">
                    <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.1</span></div>
                    <div class="titulo-puntuacion">
                        <span>Juego 4</span>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                    </div>
                </div>
                <div class="elemento-carousel">
                    <div class="portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.4</span></div>
                    <div class="titulo-puntuacion">
                        <span>Juego 5</span>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                    </div>
                </div>
            </div>
            <button class="flecha next"><i class="fa-solid fa-angle-right"></i></button>
        </div>
    </section>

    <section class="resenas-recientes">
        <h2>Reseñas recientes</h2>
        <div class="carousel">
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
                        <span> por Usuario2</span>
                    </div>
                    <p class="fecha">22 febrero 2026</p>
                </div>
                <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt tenetur?
                    Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                </p>
                <p class="username">Usuario1</p>
            </div>
            <div class="elemento-carousel">
                <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                <div class="nombre-puntuacion">
                    <h4>Juego 2</h4>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                </div>
                <div class="puntuacion-tablet">
                    <div class="titulo-puntuacion-wrapper">
                        <p class="titulo-plataforma"><strong>Juego 2</strong> en <strong>PC</strong></p>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                        <span> por Usuario2</span>
                    </div>
                    <p class="fecha">19 febrero 2026</p>
                </div>
                <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt tenetur?
                    Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                </p>
                <p class="username">Usuario3</p>
            </div>
            <div class="elemento-carousel mini-resena">
                <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                <div class="nombre-puntuacion">
                    <h4>Juego 3</h4>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                </div>
                <div class="puntuacion-tablet">
                    <div class="titulo-puntuacion-wrapper">
                        <p class="titulo-plataforma"><strong>Juego 3</strong> en <strong>PlayStation 5</strong></p>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                        <span> por Usuario3</span>
                    </div>
                    <p class="fecha">16 febrero 2026</p>
                </div>
                <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt tenetur?
                    Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                </p>
                <p class="username">Usuario3</p>
            </div>
            <div class="elemento-carousel mini-resena">
                <div class="mini-portada"><img src="/assets/img/covers/expedition33.jpg" alt="Portada"></div>
                <div class="nombre-puntuacion">
                    <h4>Juego 4</h4>
                    <div class="puntuacion"><i class="fa-solid fa-star"></i><span>4.0</span></div>
                </div>
                <div class="puntuacion-tablet">
                    <div class="titulo-puntuacion-wrapper">
                        <p class="titulo-plataforma"><strong>Juego 4</strong> en <strong>PlayStation 5</strong></p>
                        <div class="estrellas">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i
                                class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><span></span>
                        </div>
                        <span> por Usuario4</span>
                    </div>
                    <p class="fecha">16 febrero 2026</p>
                </div>
                <p class="texto">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae, incidunt tenetur?
                    Odit.<span class="completo">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cumque dicta excepturi molestias.</span>
                </p>
                <p class="username">Usuario4</p>
            </div>
        </div>
    </section>
</main>

<?php
require 'includes/nav_inferior.php';
require 'includes/footer.php';
?>
