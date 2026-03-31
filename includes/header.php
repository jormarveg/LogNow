<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;0,700;1,400;1,700&display=swap">
    <link rel="stylesheet" href="/assets/css/main.css">
    <?php foreach ($css as $archivo): ?>
        <link rel="stylesheet" href="/assets/css/<?= $archivo ?>">
    <?php endforeach; ?>
</head>
<body>
<header>
    <div class="container">
        <div class="logo"><a href="/"><span>LogNow!</span></a></div>

        <div class="buscar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Buscar juegos...">
        </div>
        <nav>
            <ul>
                <li<?= $pagina === 'catalogo' ? ' class="active"' : '' ?>><a href="/catalogo.php">Juegos</a></li>
                <?php if (estaLogueado()): ?>
                    <li<?= $pagina === 'perfil' ? ' class="active"' : '' ?>><a href="/perfil.php">Perfil</a></li>
                    <?php if (esAdmin()): ?>
                        <li><a href="/admin/">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li<?= $pagina === 'login' ? ' class="active"' : '' ?>><a href="/login.php">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if (estaLogueado()): ?>
            <div class="btnAgregar"><i class="fa fa-add"></i><span>Registrar juego</span></div>
            <a href="/logout.php" class="btn-salir"><i class="fa-solid fa-right-from-bracket"></i></a>
        <?php endif; ?>
        <div class="info">
            <input id="check-info" type="checkbox" hidden>
            <div class="contenido-info">
                <ul>
                    <li><a href="#">Acerca de</a></li>
                    <li><a href="#">Privacidad</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
                <?php if (estaLogueado()): ?>
                    <div class="session-info">
                        <a href="/logout.php">Cerrar sesión</a>
                    </div>
                <?php endif; ?>
                <div class="redes-sociales">
                    <a href="https://facebook.com"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://instagram.com"><i class="fa-brands fa-square-instagram"></i></a>
                    <a href="https://x.com"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
                <p class="copy">&copy; LogNow!</p>
            </div>
            <label for="check-info"><i class="fa-solid fa-circle-info"></i></label>
        </div>
    </div>
</header>
