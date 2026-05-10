<?php
$perfilPropio = $perfilPropio ?? false;
$urlPerfilBase = $urlPerfilBase ?? '/perfil.php';
$nickPerfil = (string) ($datosUsuario['nick'] ?? '');
$nombrePerfil = (string) ($datosUsuario['nombre'] ?? '');
$textoBioVacia = $perfilPropio ? 'Todavía no has escrito ninguna bio.' : 'Este usuario todavía no ha escrito ninguna bio.';
$textoFavoritosTitulo = $perfilPropio ? 'Tus favoritos' : 'Favoritos';
$textoFavoritosVacio = $perfilPropio ? 'Todavía no tienes favoritos' : 'Todavía no tiene favoritos';
$textoFavoritosAyuda = $perfilPropio ? 'Marca juegos de tu biblioteca como favoritos y aparecerán aquí.' : 'Cuando marque juegos como favoritos, aparecerán aquí.';
$textoResenasTitulo = $perfilPropio ? 'Tus reseñas recientes' : 'Reseñas recientes';
$textoResenasVacio = $perfilPropio ? 'Todavía no has publicado reseñas' : 'Todavía no ha publicado reseñas';
$textoResenasAyuda = $perfilPropio ? 'Cuando completes tu primera reseña desde un juego guardado en biblioteca, aparecerá aquí.' : 'Cuando publique una reseña, aparecerá aquí.';
?>

<section class="encabezado-perfil" style="background-image: url('<?= htmlspecialchars(urlEncabezadoUsuario($datosUsuario['encabezado'] ?? '')) ?>');">
    <div class="container">
        <div class="foto-perfil">
            <img src="<?= htmlspecialchars(urlAvatarUsuario($datosUsuario['avatar'] ?? '')) ?>" alt="Foto de perfil de <?= htmlspecialchars($nickPerfil) ?>">
        </div>
        <div class="datos-encabezado-perfil">
            <h1 class="nombre"><?= htmlspecialchars($nombrePerfil) ?></h1>
            <p class="nick-perfil">@<?= htmlspecialchars($nickPerfil) ?></p>
            <?php if ($perfilPropio): ?>
                <a class="boton-editar-perfil" href="/editar-perfil.php">Editar perfil</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<nav class="profile-tabs">
    <div class="container">
        <ul>
            <li<?= $tab === 'perfil' ? ' class="active"' : '' ?>><a href="<?= htmlspecialchars(urlPerfilTab($urlPerfilBase, 'perfil')) ?>"><i class="fa-solid fa-user"></i>Perfil</a></li>
            <li<?= $tab === 'juegos' ? ' class="active"' : '' ?>><a href="<?= htmlspecialchars(urlPerfilTab($urlPerfilBase, 'juegos')) ?>"><i class="fa-solid fa-gamepad"></i><?= $perfilPropio ? 'Tus juegos' : 'Juegos' ?></a></li>
            <li<?= $tab === 'resenas' ? ' class="active"' : '' ?>><a href="<?= htmlspecialchars(urlPerfilTab($urlPerfilBase, 'resenas')) ?>"><i class="fa-solid fa-message"></i><?= $perfilPropio ? 'Tus reseñas' : 'Reseñas' ?></a></li>
            <?php if ($perfilPropio): ?>
                <li><a class="editar" href="/editar-perfil.php">Editar perfil</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="container">
    <div class="content-grid">
        <aside class="sidebar">
            <section class="bio">
                <h2>Bio</h2>
                <p><?= $datosUsuario['biografia'] ? nl2br(htmlspecialchars($datosUsuario['biografia'])) : $textoBioVacia ?></p>
            </section>

            <section class="stats">
                <div class="jugados">
                    <h3>Jugados</h3>
                    <span class="datos"><?= $resumenBiblioteca['total'] ?></span>
                </div>
                <div class="este-ano">
                    <h3>Este año</h3>
                    <span class="datos"><?= $jugadosEsteAno ?></span>
                </div>
            </section>

            <section class="puntuaciones">
                <h3><?= $perfilPropio ? 'Tus puntuaciones' : 'Puntuaciones' ?></h3>
                <div class="grafica">
                    <div class="barra barra1" style="height: <?= alturaBarraPerfil($histogramaUsuario[1], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra2" style="height: <?= alturaBarraPerfil($histogramaUsuario[2], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra3" style="height: <?= alturaBarraPerfil($histogramaUsuario[3], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra4" style="height: <?= alturaBarraPerfil($histogramaUsuario[4], $maximoHistograma) ?>%;"></div>
                    <div class="barra barra5" style="height: <?= alturaBarraPerfil($histogramaUsuario[5], $maximoHistograma) ?>%;"></div>
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
            <?php if ($perfilPropio && isset($_GET['editado']) && $_GET['editado'] === 'ok'): ?>
                <p class="exito exito-perfil">Perfil actualizado correctamente.</p>
            <?php endif; ?>

            <?php if ($tab === 'perfil'): ?>
                <section class="favoritos">
                    <h2><?= $textoFavoritosTitulo ?></h2>
                    <?php if ($favoritosUsuario): ?>
                        <div class="carousel">
                            <?php foreach ($favoritosUsuario as $favorito): ?>
                                <a class="favorito elemento-carousel" href="/juego.php?id=<?= (int) $favorito['igdb_id'] ?>">
                                    <div class="portada"><img src="<?= htmlspecialchars(urlPortadaJuego($favorito['portada_url'] ?? '', $favorito['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($favorito['titulo']) ?>"></div>
                                    <p><?= htmlspecialchars($favorito['titulo']) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2><?= $textoFavoritosVacio ?></h2>
                            <p><?= $textoFavoritosAyuda ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="resenas-recientes">
                    <h2><?= $textoResenasTitulo ?></h2>
                    <?php if ($resenasUsuarioPerfil): ?>
                        <div class="carousel">
                            <?php foreach ($resenasUsuarioPerfil as $resena): ?>
                                <?php [$textoCorto, $textoCompleto] = partesTextoPerfilResena($resena['comentario']); ?>
                                <div class="elemento-carousel mini-resena">
                                    <div class="mini-portada"><img src="<?= htmlspecialchars(urlPortadaJuego($resena['portada_url'] ?? '', $resena['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
                                    <div class="nombre-puntuacion">
                                        <h4><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="puntuacion"><i class="fa-solid fa-star"></i><span><?= puntuacionPerfilVisible($resena['puntuacion_estrellas']) ?></span></div>
                                    </div>
                                    <div class="puntuacion-tablet">
                                        <div class="titulo-puntuacion-wrapper">
                                            <p class="titulo-plataforma">
                                                <strong><?= htmlspecialchars($resena['titulo']) ?></strong>
                                                <?php if (!empty($resena['plataforma'])): ?>
                                                    en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                                <?php endif; ?>
                                            </p>
                                            <div class="estrellas"><?= estrellasPerfil($resena['puntuacion_estrellas']) ?></div>
                                        </div>
                                        <p class="fecha"><?= fechaPerfilBonita($resena['fecha_publicacion']) ?></p>
                                    </div>
                                    <p class="texto"><?= htmlspecialchars($textoCorto) ?><?php if ($textoCompleto !== ''): ?><span class="completo"><?= htmlspecialchars($textoCompleto) ?></span><?php endif; ?></p>
                                    <p class="username"><?= htmlspecialchars($nickPerfil) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2><?= $textoResenasVacio ?></h2>
                            <p><?= $textoResenasAyuda ?></p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php elseif ($tab === 'juegos'): ?>
                <?php
                $baseBibliotecaUrl = urlPerfilTab($urlPerfilBase, 'juegos');
                $bibliotecaEyebrow = $perfilPropio ? 'Biblioteca personal' : 'Biblioteca de @' . $nickPerfil;
                $bibliotecaTitulo = $perfilPropio ? 'Mis juegos' : 'Juegos';
                $bibliotecaTexto = $perfilPropio ? 'Consulta tu biblioteca, filtra por estado y revisa rápido lo que tienes en marcha.' : 'Consulta los juegos que tiene guardados este usuario y su estado.';
                $bibliotecaMostrarAccion = $perfilPropio;
                $bibliotecaVaciaTitulo = $perfilPropio ? 'Tu biblioteca está vacía' : 'Esta biblioteca está vacía';
                $bibliotecaVaciaTexto = $perfilPropio ? 'Todavía no has registrado ningún juego. Empieza desde el catálogo y guarda el primero.' : 'Este usuario todavía no ha registrado juegos en su biblioteca.';
                $bibliotecaFiltroVacioTexto = $perfilPropio ? 'Prueba a cambiar el estado seleccionado para ver el resto de tu biblioteca.' : 'Prueba a cambiar el estado seleccionado para ver el resto de su biblioteca.';
                require __DIR__ . '/bloque-mis-juegos.php';
                ?>
            <?php else: ?>
                <section class="resenas-recientes">
                    <h2><?= $textoResenasTitulo ?></h2>
                    <?php if ($resenasUsuarioTab): ?>
                        <div class="carousel">
                            <?php foreach ($resenasUsuarioTab as $resena): ?>
                                <?php [$textoCorto, $textoCompleto] = partesTextoPerfilResena($resena['comentario']); ?>
                                <div class="elemento-carousel mini-resena">
                                    <div class="mini-portada"><img src="<?= htmlspecialchars(urlPortadaJuego($resena['portada_url'] ?? '', $resena['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($resena['titulo']) ?>"></div>
                                    <div class="nombre-puntuacion">
                                        <h4><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="puntuacion"><i class="fa-solid fa-star"></i><span><?= puntuacionPerfilVisible($resena['puntuacion_estrellas']) ?></span></div>
                                    </div>
                                    <div class="puntuacion-tablet">
                                        <div class="titulo-puntuacion-wrapper">
                                            <p class="titulo-plataforma">
                                                <strong><?= htmlspecialchars($resena['titulo']) ?></strong>
                                                <?php if (!empty($resena['plataforma'])): ?>
                                                    en <strong><?= htmlspecialchars($resena['plataforma']) ?></strong>
                                                <?php endif; ?>
                                            </p>
                                            <div class="estrellas"><?= estrellasPerfil($resena['puntuacion_estrellas']) ?></div>
                                        </div>
                                        <p class="fecha"><?= fechaPerfilBonita($resena['fecha_publicacion']) ?></p>
                                    </div>
                                    <p class="texto"><?= htmlspecialchars($textoCorto) ?><?php if ($textoCompleto !== ''): ?><span class="completo"><?= htmlspecialchars($textoCompleto) ?></span><?php endif; ?></p>
                                    <p class="username"><?= htmlspecialchars($nickPerfil) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel-vacio">
                            <h2><?= $textoResenasVacio ?></h2>
                            <p><?= $textoResenasAyuda ?></p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </section>
    </div>
</main>
