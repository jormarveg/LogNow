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
                <li<?= $pagina === 'perfil' ? ' class="active"' : '' ?>><a href="/pages/perfil.php">Perfil</a></li>
                <li<?= $pagina === 'catalogo' ? ' class="active"' : '' ?>><a href="/pages/catalogo.php">Juegos</a></li>
            </ul>
        </nav>
        <div class="btnAgregar"><i class="fa fa-add"></i><span>Registrar juego</span></div>
        <div class="info">
            <input id="check-info" type="checkbox" hidden>
            <div class="contenido-info">
                <ul>
                    <li><a href="#">Acerca de</a></li>
                    <li><a href="#">Privacidad</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
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
