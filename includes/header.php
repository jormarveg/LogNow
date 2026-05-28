<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/img/favicon/site.webmanifest">
    <link rel="shortcut icon" href="/assets/img/favicon/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Limelight&family=Poppins:ital,wght@0,400;0,600;0,700;1,400;1,700&display=swap">
    <?php if (isset($cssExterno) && !empty($cssExterno)): ?>
        <?php foreach ($cssExterno as $archivo): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($archivo) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php $versionMain = filemtime(__DIR__ . '/../assets/css/main.css'); ?>
    <link rel="stylesheet" href="/assets/css/main.css?v=<?= $versionMain ?>">
    <?php foreach ($css as $archivo): ?>
        <?php $rutaCss = __DIR__ . '/../assets/css/' . $archivo; ?>
        <?php $versionCss = file_exists($rutaCss) ? filemtime($rutaCss) : time(); ?>
        <link rel="stylesheet" href="/assets/css/<?= $archivo ?>?v=<?= $versionCss ?>">
    <?php endforeach; ?>
</head>
<body>
<header>
    <div class="container">
        <div class="logo"><a href="/"><span class="marca-lognow">LogNow!</span></a></div>

        <form class="buscar" action="/buscar.php" method="GET">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Buscar juegos..." value="<?= htmlspecialchars($busquedaHeader ?? '') ?>">
        </form>
        <nav>
            <ul>
                <li<?= $pagina === 'catalogo' ? ' class="active"' : '' ?>><a href="/catalogo.php">Juegos</a></li>
                <?php if (estaLogueado()): ?>
                    <li<?= in_array($pagina, ['mis-juegos', 'registrar-juego'], true) ? ' class="active"' : '' ?>><a href="/perfil.php?tab=juegos">Mis juegos</a></li>
                    <li<?= $pagina === 'perfil' ? ' class="active"' : '' ?>><a href="/perfil.php">Perfil</a></li>
                    <?php if (esAdmin()): ?>
                        <li<?= $pagina === 'admin' ? ' class="active"' : '' ?>><a href="/admin/">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li<?= $pagina === 'login' ? ' class="active"' : '' ?>><a href="/login.php">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if (estaLogueado()): ?>
            <a href="/logout.php" class="btn-salir"><i class="fa-solid fa-right-from-bracket"></i></a>
        <?php endif; ?>
        <div class="info">
            <input id="check-info" type="checkbox" hidden>
            <div class="contenido-info">
                <ul>
                    <li><a href="/acerca.php">Acerca de</a></li>
                    <li><a href="/privacidad.php">Privacidad</a></li>
                </ul>
                <?php if (estaLogueado()): ?>
                    <?php if (esAdmin()): ?>
                        <div class="session-info">
                            <a href="/admin/">Panel admin</a>
                        </div>
                    <?php endif; ?>
                    <div class="session-info">
                        <a href="/logout.php">Cerrar sesión</a>
                    </div>
                <?php endif; ?>
                <div class="redes-sociales">
                    <a href="https://facebook.com"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://instagram.com"><i class="fa-brands fa-square-instagram"></i></a>
                    <a href="https://x.com"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
                <p class="copy">&copy; <span class="marca-lognow">LogNow!</span></p>
            </div>
            <label for="check-info"><i class="fa-solid fa-circle-info"></i></label>
        </div>
    </div>
</header>
