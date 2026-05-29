<?php
require __DIR__ . '/includes/proteger.php';
require __DIR__ . '/includes/funciones.php';
require __DIR__ . '/../includes/perfil_helpers.php';

$totalJuegosCatalogo = (int) $db->query('SELECT COUNT(*) FROM VIDEOJUEGO')->fetchColumn();

$ultimosUsuarios = $db->query("SELECT nombre, nick, rol, activo, registro
                               FROM USUARIO
                               ORDER BY registro DESC, id DESC
                               LIMIT 5")->fetchAll();

$ultimasResenas = $db->query("SELECT r.puntuacion, r.fecha_publicacion, u.nick, v.titulo, v.igdb_id
                              FROM RESENA r
                              INNER JOIN USUARIO u ON u.id = r.id_usuario
                              INNER JOIN VIDEOJUEGO v ON v.id = r.id_videojuego
                              WHERE r.activa = 1
                                AND r.comentario IS NOT NULL
                                AND TRIM(r.comentario) <> ''
                              ORDER BY r.fecha_publicacion DESC
                              LIMIT 5")->fetchAll();

$juegosPopulares = $db->query("SELECT v.titulo, v.igdb_id, COUNT(uj.id) AS total
                               FROM USUARIO_JUEGO uj
                               INNER JOIN VIDEOJUEGO v ON v.id = uj.id_videojuego
                               GROUP BY v.id, v.titulo, v.igdb_id
                               ORDER BY total DESC, v.titulo ASC
                               LIMIT 5")->fetchAll();

$titulo = 'Panel admin — LogNow!';
$css = ['admin.css'];
$js = ['admin.js'];
$pagina = 'admin';
$adminPagina = 'inicio';
require __DIR__ . '/../includes/header.php';
?>

<main class="container admin-page">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>

    <section class="admin-content">
        <section class="admin-cabecera">
            <h1>Panel de administración</h1>
        </section>

        <?php if ($totalJuegosCatalogo === 0): ?>
            <section class="admin-importar">
                <div>
                    <h2>Catálogo vacío</h2>
                    <p>Todavía no hay juegos importados desde IGDB.</p>
                </div>
                <a class="boton-importar-admin" href="/api/importar.php?pagina=1&cantidad=500&reiniciar=1">Importar juegos</a>
            </section>
        <?php endif; ?>

        <div class="admin-columnas">
            <section class="admin-bloque">
                <div class="admin-titulo-bloque">
                    <h2>Últimos usuarios</h2>
                    <a href="/admin/usuarios.php">Ver todos</a>
                </div>
                <?php if ($ultimosUsuarios): ?>
                    <div class="lista-admin">
                        <?php foreach ($ultimosUsuarios as $usuario): ?>
                            <article>
                                <div>
                                    <strong><a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($usuario['nick'])) ?>"><?= htmlspecialchars($usuario['nombre']) ?></a></strong>
                                    <span><a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($usuario['nick'])) ?>">@<?= htmlspecialchars($usuario['nick']) ?></a> · <?= adminFecha($usuario['registro']) ?></span>
                                </div>
                                <span class="estado-cuenta estado-<?= $usuario['activo'] ? 'activo' : 'inactivo' ?>"><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-vacio">
                        <p>Todavía no hay usuarios registrados.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="admin-bloque">
                <div class="admin-titulo-bloque">
                    <h2>Reseñas recientes</h2>
                </div>
                <?php if ($ultimasResenas): ?>
                    <div class="lista-admin">
                        <?php foreach ($ultimasResenas as $resena): ?>
                            <article>
                                <div>
                                    <strong><a class="enlace-admin-juego" href="/juego.php?id=<?= (int) $resena['igdb_id'] ?>" title="<?= htmlspecialchars($resena['titulo']) ?>"><?= htmlspecialchars(adminTextoCorto($resena['titulo'])) ?></a></strong>
                                    <span><a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($resena['nick'])) ?>">@<?= htmlspecialchars($resena['nick']) ?></a> · <?= adminFecha($resena['fecha_publicacion']) ?></span>
                                </div>
                                <div class="puntuacion-admin">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= adminPuntuacion($resena['puntuacion']) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-vacio">
                        <p>Todavía no hay reseñas publicadas.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="admin-bloque admin-bloque-ancho">
                <div class="admin-titulo-bloque">
                    <h2>Juegos más guardados</h2>
                </div>
                <?php if ($juegosPopulares): ?>
                    <div class="lista-admin">
                        <?php foreach ($juegosPopulares as $juego): ?>
                            <article>
                                <div>
                                    <strong><a class="enlace-admin-juego" href="/juego.php?id=<?= (int) $juego['igdb_id'] ?>" title="<?= htmlspecialchars($juego['titulo']) ?>"><?= htmlspecialchars(adminTextoCorto($juego['titulo'])) ?></a></strong>
                                </div>
                                <span class="dato-admin"><?= (int) $juego['total'] ?> veces</span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="admin-vacio">
                        <p>Todavía no hay juegos guardados en bibliotecas.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/../includes/nav_inferior.php';
require __DIR__ . '/../includes/footer.php';
?>
