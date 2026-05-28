<?php
require __DIR__ . '/includes/proteger.php';
require __DIR__ . '/includes/funciones.php';
require __DIR__ . '/../includes/perfil_helpers.php';

function volverReportes($tipo, $valor) {
    header('Location: /admin/reportes.php?' . $tipo . '=' . urlencode($valor));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idReporte = (int) ($_POST['id_reporte'] ?? 0);

    if ($idReporte <= 0) {
        volverReportes('error', 'datos');
    }

    if ($accion === 'ignorar') {
        $stmt = $db->prepare("UPDATE REPORTE SET estado = 'descartado' WHERE id = ? AND estado = 'pendiente'");
        $stmt->execute([$idReporte]);
        volverReportes('ok', 'ignorado');
    }

    if ($accion === 'eliminar') {
        $stmtReporte = $db->prepare("SELECT id_resena FROM REPORTE WHERE id = ? AND estado = 'pendiente' LIMIT 1");
        $stmtReporte->execute([$idReporte]);
        $idResena = (int) $stmtReporte->fetchColumn();

        if ($idResena <= 0) {
            volverReportes('error', 'datos');
        }

        $db->beginTransaction();

        $stmtReportes = $db->prepare('DELETE FROM REPORTE WHERE id_resena = ?');
        $stmtReportes->execute([$idResena]);

        $stmtResena = $db->prepare('UPDATE RESENA
                                    SET comentario = NULL
                                    WHERE id = ?');
        $stmtResena->execute([$idResena]);

        $db->commit();
        volverReportes('ok', 'eliminado');
    }

    volverReportes('error', 'datos');
}

$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPagina = 8;
$stmtTotal = $db->query("SELECT COUNT(*)
                         FROM REPORTE rep
                         INNER JOIN RESENA r ON r.id = rep.id_resena
                         WHERE rep.estado = 'pendiente' AND r.activa = 1");
$totalReportes = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalReportes / $porPagina));

if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}

$offset = ($paginaActual - 1) * $porPagina;
$stmtReportes = $db->prepare("SELECT rep.id, rep.id_resena, rep.motivo, rep.fecha,
                                     r.comentario, r.fecha_publicacion,
                                     autor.nick AS autor_nick, autor.nombre AS autor_nombre,
                                     reporta.nick AS reporta_nick, reporta.nombre AS reporta_nombre,
                                     v.titulo, v.igdb_id
                              FROM REPORTE rep
                              INNER JOIN RESENA r ON r.id = rep.id_resena
                              INNER JOIN USUARIO autor ON autor.id = r.id_usuario
                              INNER JOIN USUARIO reporta ON reporta.id = rep.id_usuario
                              INNER JOIN VIDEOJUEGO v ON v.id = r.id_videojuego
                              WHERE rep.estado = 'pendiente' AND r.activa = 1
                              ORDER BY rep.fecha DESC, rep.id DESC
                              LIMIT $porPagina OFFSET $offset");
$stmtReportes->execute();
$reportes = $stmtReportes->fetchAll();

$ok = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';
$mensajesOk = [
    'ignorado' => 'Reporte ignorado correctamente.',
    'eliminado' => 'Comentario eliminado correctamente.'
];
$mensajesError = [
    'datos' => 'No se ha podido guardar la acción.'
];

$titulo = 'Reportes — LogNow!';
$css = ['admin.css'];
$js = ['admin.js'];
$pagina = 'admin';
$adminPagina = 'reportes';
require __DIR__ . '/../includes/header.php';
?>

<main class="container admin-page">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>

    <section class="admin-content">
        <section class="admin-cabecera">
            <h1>Reportes</h1>
            <p>Revisa los comentarios reportados y decide si se eliminan o se ignoran.</p>
        </section>

        <?php if (isset($mensajesOk[$ok])): ?>
            <p class="mensaje-admin mensaje-ok"><?= $mensajesOk[$ok] ?></p>
        <?php endif; ?>

        <?php if (isset($mensajesError[$error])): ?>
            <p class="mensaje-admin mensaje-error"><?= $mensajesError[$error] ?></p>
        <?php endif; ?>

        <section class="admin-bloque">
            <?php if ($reportes): ?>
                <div class="reportes-admin">
                    <?php foreach ($reportes as $reporte): ?>
                        <article class="reporte-admin">
                            <div class="cabecera-reporte-admin">
                                <h2><a class="enlace-admin-juego" href="/juego.php?id=<?= (int) $reporte['igdb_id'] ?>"><?= htmlspecialchars($reporte['titulo']) ?></a></h2>
                                <p>
                                    Comentario de
                                    <a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($reporte['autor_nick'])) ?>">@<?= htmlspecialchars($reporte['autor_nick']) ?></a>
                                    · Reportado por
                                    <a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($reporte['reporta_nick'])) ?>">@<?= htmlspecialchars($reporte['reporta_nick']) ?></a>
                                    · <?= adminFecha($reporte['fecha']) ?>
                                </p>
                            </div>

                            <div class="detalle-reporte-admin">
                                <div>
                                    <h3>Comentario</h3>
                                    <p><?= nl2br(htmlspecialchars($reporte['comentario'])) ?></p>
                                </div>
                                <div>
                                    <h3>Motivo</h3>
                                    <p><?= htmlspecialchars($reporte['motivo']) ?></p>
                                </div>
                            </div>

                            <div class="acciones-reporte-admin">
                                <form method="POST" class="form-confirmacion-admin" data-titulo="Ignorar reporte" data-texto="El reporte quedará descartado y la reseña seguirá visible." data-confirmar="Ignorar">
                                    <input type="hidden" name="accion" value="ignorar">
                                    <input type="hidden" name="id_reporte" value="<?= (int) $reporte['id'] ?>">
                                    <button class="boton-estado abrir-confirmacion-admin" type="button">Ignorar</button>
                                </form>
                                <form method="POST" class="form-confirmacion-admin" data-titulo="Eliminar comentario" data-texto="Se eliminará el comentario reportado y sus reportes asociados. La puntuación se conservará." data-confirmar="Eliminar comentario">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_reporte" value="<?= (int) $reporte['id'] ?>">
                                    <button class="boton-eliminar abrir-confirmacion-admin" type="button">Eliminar comentario</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="admin-vacio">
                    <p>No hay reportes pendientes.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion-admin">
                <?php if ($paginaActual > 1): ?>
                    <a href="/admin/reportes.php?p=<?= $paginaActual - 1 ?>">Anterior</a>
                <?php endif; ?>

                <?php foreach (paginasCompactas($paginaActual, $totalPaginas) as $item): ?>
                    <?php if ($item === '...'): ?>
                        <span class="separador">...</span>
                    <?php else: ?>
                        <a href="/admin/reportes.php?p=<?= $item ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="/admin/reportes.php?p=<?= $paginaActual + 1 ?>">Siguiente</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<div class="modal-admin-confirmacion" id="modalAdminConfirmacion" hidden>
    <div class="modal-admin-fondo"></div>
    <div class="modal-admin-panel" role="dialog" aria-modal="true" aria-labelledby="tituloModalAdmin">
        <h2 id="tituloModalAdmin">Confirmar acción</h2>
        <p id="textoModalAdmin"></p>
        <div class="acciones-modal-admin">
            <button type="button" class="boton-cancelar-admin">Cancelar</button>
            <button type="button" class="boton-confirmar-admin">Confirmar</button>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/nav_inferior.php';
require __DIR__ . '/../includes/footer.php';
?>
