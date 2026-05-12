<?php
$baseBibliotecaUrl = $baseBibliotecaUrl ?? '/mis-juegos.php';
$bibliotecaEyebrow = $bibliotecaEyebrow ?? 'Biblioteca personal';
$bibliotecaTitulo = $bibliotecaTitulo ?? 'Mis juegos';
$bibliotecaTexto = $bibliotecaTexto ?? 'Consulta tu biblioteca y filtra por estado.';
$bibliotecaMostrarAccion = $bibliotecaMostrarAccion ?? true;
$bibliotecaAccionTexto = $bibliotecaAccionTexto ?? 'Registrar otro juego';
$bibliotecaVaciaTitulo = $bibliotecaVaciaTitulo ?? 'Tu biblioteca está vacía';
$bibliotecaVaciaTexto = $bibliotecaVaciaTexto ?? 'Todavía no has registrado ningún juego. Empieza desde el catálogo y guarda el primero.';
$bibliotecaFiltroVacioTexto = $bibliotecaFiltroVacioTexto ?? 'Prueba a cambiar el estado seleccionado para ver el resto de tu biblioteca.';
?>
<section class="cabecera-biblioteca">
    <div>
        <p class="eyebrow"><?= htmlspecialchars($bibliotecaEyebrow) ?></p>
        <h2><?= htmlspecialchars($bibliotecaTitulo) ?></h2>
        <p class="texto-cabecera"><?= htmlspecialchars($bibliotecaTexto) ?></p>
    </div>
    <?php if ($bibliotecaMostrarAccion): ?>
        <a class="boton-principal" href="/catalogo.php"><?= htmlspecialchars($bibliotecaAccionTexto) ?></a>
    <?php endif; ?>
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
                    <img src="<?= htmlspecialchars(urlPortadaJuego($juego['portada_url'] ?? '', $juego['titulo'])) ?>" alt="Portada de <?= htmlspecialchars($juego['titulo']) ?>">
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
                        <?php if (!empty($juego['fecha_inicio'])): ?>
                            <p>Inicio: <?= fechaBibliotecaBonita($juego['fecha_inicio']) ?></p>
                        <?php endif; ?>
                        <?php if ($juego['estado'] === 'completado' && !empty($juego['fecha_fin'])): ?>
                            <p>Fin: <?= fechaBibliotecaBonita($juego['fecha_fin']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="panel-vacio">
            <?php if ($resumenBiblioteca['total'] > 0): ?>
                <h2>No hay juegos con ese filtro</h2>
                <p><?= htmlspecialchars($bibliotecaFiltroVacioTexto) ?></p>
            <?php else: ?>
                <h2><?= htmlspecialchars($bibliotecaVaciaTitulo) ?></h2>
                <p><?= htmlspecialchars($bibliotecaVaciaTexto) ?></p>
            <?php endif; ?>
            <?php if ($bibliotecaMostrarAccion): ?>
                <a class="boton-secundario" href="/catalogo.php">Ir al catálogo</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (($totalPaginasBiblioteca ?? 1) > 1): ?>
    <nav class="paginacion paginacion-biblioteca">
        <?php if (($paginaBibliotecaActual ?? 1) > 1): ?>
            <a href="<?= htmlspecialchars(urlBibliotecaPagina($baseBibliotecaUrl, $estadoFiltro, $paginaBibliotecaActual - 1)) ?>">Anterior</a>
        <?php endif; ?>

        <?php foreach (paginasBiblioteca($paginaBibliotecaActual, $totalPaginasBiblioteca) as $item): ?>
            <?php if ($item === '...'): ?>
                <span class="separador">...</span>
            <?php else: ?>
                <a href="<?= htmlspecialchars(urlBibliotecaPagina($baseBibliotecaUrl, $estadoFiltro, $item)) ?>"<?= $item === $paginaBibliotecaActual ? ' class="active"' : '' ?>><?= $item ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginaBibliotecaActual < $totalPaginasBiblioteca): ?>
            <a href="<?= htmlspecialchars(urlBibliotecaPagina($baseBibliotecaUrl, $estadoFiltro, $paginaBibliotecaActual + 1)) ?>">Siguiente</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
