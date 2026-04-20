<?php
$baseBibliotecaUrl = $baseBibliotecaUrl ?? '/mis-juegos.php';
?>
<section class="cabecera-biblioteca">
    <div>
        <p class="eyebrow">Biblioteca personal</p>
        <h2>Mis juegos</h2>
        <p class="texto-cabecera">Consulta tu biblioteca, filtra por estado y revisa rápido lo que tienes en marcha.</p>
    </div>
    <a class="boton-principal" href="/catalogo.php">Registrar otro juego</a>
</section>

<section class="tarjetas-resumen">
    <article class="tarjeta-resumen">
        <span class="valor-resumen"><?= $resumenBiblioteca['total'] ?></span>
        <span class="label-resumen">Totales</span>
    </article>
    <article class="tarjeta-resumen">
        <span class="valor-resumen"><?= $resumenBiblioteca['favoritos'] ?></span>
        <span class="label-resumen">Favoritos</span>
    </article>
    <article class="tarjeta-resumen">
        <span class="valor-resumen"><?= $resumenBiblioteca['jugando'] ?></span>
        <span class="label-resumen">Jugando</span>
    </article>
    <article class="tarjeta-resumen">
        <span class="valor-resumen"><?= $resumenBiblioteca['completados'] ?></span>
        <span class="label-resumen">Completados</span>
    </article>
</section>

<nav class="filtros-biblioteca">
    <?php foreach ($filtros as $clave => $texto): ?>
        <a href="<?= htmlspecialchars(urlBibliotecaEstado($baseBibliotecaUrl, $clave)) ?>"<?= $estadoFiltro === $clave ? ' class="active"' : '' ?>>
            <span><?= $texto ?></span>
            <strong><?= $contadorFiltros[$clave] ?? 0 ?></strong>
        </a>
    <?php endforeach; ?>
</nav>

<section class="grid-biblioteca">
    <?php if ($juegosBiblioteca): ?>
        <?php foreach ($juegosBiblioteca as $juego): ?>
            <article class="tarjeta-biblioteca">
                <a class="portada-biblioteca" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">
                    <img src="<?= htmlspecialchars($juego['portada_url'] ?: '/assets/img/covers/expedition33.jpg') ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
                    <?php if (!empty($juego['favorito'])): ?>
                        <span class="favorito-biblioteca"><i class="fa-solid fa-heart"></i></span>
                    <?php endif; ?>
                </a>

                <div class="contenido-biblioteca">
                    <div class="cabecera-tarjeta-biblioteca">
                        <h2><a href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>"><?= htmlspecialchars($juego['titulo']) ?></a></h2>
                        <span class="estado-biblioteca estado-<?= htmlspecialchars($juego['estado']) ?>"><?= textoEstadoBiblioteca($juego['estado']) ?></span>
                    </div>

                    <p class="plataforma-biblioteca"><?= htmlspecialchars($juego['plataforma']) ?></p>

                    <div class="meta-biblioteca">
                        <p><?= tiempoBiblioteca($juego['horas_jugadas'], $juego['minutos_jugados']) ?></p>
                        <?php if ($juego['estado'] !== 'pendiente' || !empty($juego['fecha_inicio'])): ?>
                            <p>Inicio: <?= fechaBibliotecaBonita($juego['fecha_inicio']) ?></p>
                        <?php endif; ?>
                        <?php if ($juego['estado'] === 'completado' && !empty($juego['fecha_fin'])): ?>
                            <p>Fin: <?= fechaBibliotecaBonita($juego['fecha_fin']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="pie-biblioteca">
                        <?php if ($juego['puntuacion_usuario'] !== null): ?>
                            <p class="nota-biblioteca"><i class="fa-solid fa-star"></i> <?= puntuacionBibliotecaVisible($juego['puntuacion_usuario']) ?></p>
                        <?php else: ?>
                            <p class="nota-biblioteca sin-nota">Sin reseña todavía</p>
                        <?php endif; ?>

                        <div class="acciones-tarjeta-biblioteca">
                            <a class="enlace-detalle-biblioteca" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>">Ver ficha</a>
                            <a class="enlace-detalle-biblioteca" href="/registrar-juego.php?id=<?= (int) $juego['igdb_id'] ?>&editar=1">Editar</a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="panel-vacio">
            <?php if ($resumenBiblioteca['total'] > 0): ?>
                <h2>No hay juegos con ese filtro</h2>
                <p>Prueba a cambiar el estado seleccionado para ver el resto de tu biblioteca.</p>
            <?php else: ?>
                <h2>Tu biblioteca está vacía</h2>
                <p>Todavía no has registrado ningún juego. Empieza desde el catálogo y guarda el primero.</p>
            <?php endif; ?>
            <a class="boton-secundario" href="/catalogo.php">Ir al catálogo</a>
        </div>
    <?php endif; ?>
</section>
