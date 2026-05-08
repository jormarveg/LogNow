<?php
require __DIR__ . '/includes/proteger.php';
require __DIR__ . '/includes/funciones.php';
require __DIR__ . '/../includes/perfil_helpers.php';

function urlUsuarios($cambios = []) {
    $params = $_GET;
    unset($params['ok'], $params['error']);

    foreach ($cambios as $clave => $valor) {
        if ($valor === null || $valor === '' || $valor === 0) {
            unset($params[$clave]);
        } else {
            $params[$clave] = $valor;
        }
    }

    $query = http_build_query($params);

    return '/admin/usuarios.php' . ($query ? '?' . $query : '');
}

function volverUsuarios($url, $tipo, $valor) {
    if (strpos($url, '/admin/usuarios.php') !== 0) {
        $url = '/admin/usuarios.php';
    }

    $separador = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separador . $tipo . '=' . urlencode($valor));
    exit;
}

$paramsVolver = $_GET;
unset($paramsVolver['ok'], $paramsVolver['error']);
$queryVolver = http_build_query($paramsVolver);
$urlVolver = '/admin/usuarios.php' . ($queryVolver ? '?' . $queryVolver : '');
$paramsPdf = $_GET;
unset($paramsPdf['ok'], $paramsPdf['error'], $paramsPdf['p']);
$queryPdf = http_build_query($paramsPdf);
$urlPdf = '/admin/exportar-pdf.php' . ($queryPdf ? '?' . $queryPdf : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $volver = $_POST['volver'] ?? '/admin/usuarios.php';
    $idAdmin = (int) getUsuario()['id'];

    if ($idUsuario <= 0) {
        volverUsuarios($volver, 'error', 'datos');
    }

    if ($idUsuario === $idAdmin) {
        volverUsuarios($volver, 'error', 'propio');
    }

    if ($accion === 'estado') {
        $activo = ($_POST['activo'] ?? '') === '1' ? 1 : 0;
        $stmt = $db->prepare('UPDATE USUARIO SET activo = ? WHERE id = ?');
        $stmt->execute([$activo, $idUsuario]);
        volverUsuarios($volver, 'ok', 'estado');
    }

    volverUsuarios($volver, 'error', 'datos');
}

$busqueda = trim($_GET['q'] ?? '');
$rolFiltro = $_GET['rol'] ?? '';
$estadoFiltro = $_GET['estado'] ?? '';
$paginaActual = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$porPagina = 12;

if (!in_array($rolFiltro, ['usuario', 'admin'], true)) {
    $rolFiltro = '';
}

if (!in_array($estadoFiltro, ['activos', 'inactivos'], true)) {
    $estadoFiltro = '';
}

$where = [];
$params = [];

if ($busqueda !== '') {
    $where[] = '(u.nombre LIKE ? OR u.nick LIKE ? OR u.email LIKE ?)';
    $textoBusqueda = '%' . $busqueda . '%';
    $params[] = $textoBusqueda;
    $params[] = $textoBusqueda;
    $params[] = $textoBusqueda;
}

if ($rolFiltro !== '') {
    $where[] = 'u.rol = ?';
    $params[] = $rolFiltro;
}

if ($estadoFiltro === 'activos') {
    $where[] = 'u.activo = 1';
} elseif ($estadoFiltro === 'inactivos') {
    $where[] = 'u.activo = 0';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmtTotal = $db->prepare("SELECT COUNT(*) FROM USUARIO u $whereSql");
$stmtTotal->execute($params);
$totalUsuarios = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalUsuarios / $porPagina));

if ($paginaActual > $totalPaginas) {
    $paginaActual = $totalPaginas;
}

$offset = ($paginaActual - 1) * $porPagina;
$stmtUsuarios = $db->prepare("SELECT u.id, u.nombre, u.nick, u.email, u.rol, u.activo, u.registro,
                                     (SELECT COUNT(*) FROM USUARIO_JUEGO uj WHERE uj.id_usuario = u.id) AS total_juegos,
                                     (SELECT COUNT(*) FROM RESENA r WHERE r.id_usuario = u.id AND r.activa = 1 AND r.comentario IS NOT NULL AND TRIM(r.comentario) <> '') AS total_resenas
                              FROM USUARIO u
                              $whereSql
                              ORDER BY u.registro DESC, u.id DESC
                              LIMIT $porPagina OFFSET $offset");
$stmtUsuarios->execute($params);
$usuarios = $stmtUsuarios->fetchAll();

$ok = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';
$mensajesOk = [
    'estado' => 'Estado actualizado correctamente.'
];
$mensajesError = [
    'propio' => 'No puedes cambiar tu propia cuenta desde aquí.',
    'datos' => 'No se han podido guardar los cambios.'
];

$titulo = 'Usuarios — LogNow!';
$css = ['admin.css'];
$pagina = 'admin';
$adminPagina = 'usuarios';
require __DIR__ . '/../includes/header.php';
?>

<main class="container admin-page">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>

    <section class="admin-content">
        <section class="admin-cabecera">
            <h1>Usuarios</h1>
        </section>

        <?php if (isset($mensajesOk[$ok])): ?>
            <p class="mensaje-admin mensaje-ok"><?= $mensajesOk[$ok] ?></p>
        <?php endif; ?>

        <?php if (isset($mensajesError[$error])): ?>
            <p class="mensaje-admin mensaje-error"><?= $mensajesError[$error] ?></p>
        <?php endif; ?>

        <form method="GET" class="admin-filtros">
            <label>
                <span>Buscar</span>
                <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Nombre, nick o email">
            </label>
            <label>
                <span>Rol</span>
                <select name="rol">
                    <option value="">Todos</option>
                    <option value="usuario"<?= $rolFiltro === 'usuario' ? ' selected' : '' ?>>Usuario</option>
                    <option value="admin"<?= $rolFiltro === 'admin' ? ' selected' : '' ?>>Admin</option>
                </select>
            </label>
            <label>
                <span>Estado</span>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="activos"<?= $estadoFiltro === 'activos' ? ' selected' : '' ?>>Activos</option>
                    <option value="inactivos"<?= $estadoFiltro === 'inactivos' ? ' selected' : '' ?>>Inactivos</option>
                </select>
            </label>
            <button type="submit">Filtrar</button>
            <a href="/admin/usuarios.php" class="boton-limpiar">Limpiar</a>
            <a href="<?= htmlspecialchars($urlPdf) ?>" class="boton-pdf">Exportar PDF</a>
        </form>

        <section class="admin-bloque">
            <?php if ($usuarios): ?>
                <div class="tabla-admin-wrapper">
                    <table class="tabla-admin">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Actividad</th>
                                <th>Registro</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php $esCuentaActual = (int) $usuario['id'] === (int) getUsuario()['id']; ?>
                                <tr>
                                    <td>
                                        <strong><a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($usuario['nick'])) ?>"><?= htmlspecialchars($usuario['nombre']) ?></a></strong>
                                        <span><a class="enlace-admin" href="<?= htmlspecialchars(urlUsuarioPublico($usuario['nick'])) ?>">@<?= htmlspecialchars($usuario['nick']) ?></a></span>
                                        <span>Rol: <?= $usuario['rol'] === 'admin' ? 'admin' : 'usuario' ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span><?= (int) $usuario['total_juegos'] ?> juegos</span>
                                        <span><?= (int) $usuario['total_resenas'] ?> reseñas</span>
                                    </td>
                                    <td><?= adminFecha($usuario['registro']) ?></td>
                                    <td>
                                        <div class="estado-admin">
                                            <span class="estado-cuenta estado-<?= $usuario['activo'] ? 'activo' : 'inactivo' ?>"><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></span>
                                            <form method="POST">
                                                <input type="hidden" name="accion" value="estado">
                                                <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id'] ?>">
                                                <input type="hidden" name="activo" value="<?= $usuario['activo'] ? '0' : '1' ?>">
                                                <input type="hidden" name="volver" value="<?= htmlspecialchars($urlVolver) ?>">
                                                <button class="boton-estado" type="submit"<?= $esCuentaActual ? ' disabled' : '' ?>>
                                                    <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-vacio">
                    <p>No hay usuarios con esos filtros.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion-admin">
                <?php if ($paginaActual > 1): ?>
                    <a href="<?= htmlspecialchars(urlUsuarios(['p' => $paginaActual - 1])) ?>">Anterior</a>
                <?php endif; ?>

                <?php foreach (adminPaginas($paginaActual, $totalPaginas) as $item): ?>
                    <?php if ($item === '...'): ?>
                        <span class="separador">...</span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(urlUsuarios(['p' => $item])) ?>"<?= $item === $paginaActual ? ' class="active"' : '' ?>><?= $item ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="<?= htmlspecialchars(urlUsuarios(['p' => $paginaActual + 1])) ?>">Siguiente</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php
require __DIR__ . '/../includes/nav_inferior.php';
require __DIR__ . '/../includes/footer.php';
?>
