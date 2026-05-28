<?php
require '../includes/auth.php';

$titulo = 'Política de privacidad — LogNow!';
$css = ['legal.css'];
$pagina = 'privacidad';
require '../includes/header.php';
?>

<main class="container">
    <section class="pagina-legal">
        <h1>Política de privacidad</h1>
        <div class="cuerpo-texto">
            <p>LogNow! es una aplicación web de seguimiento y reseñas de videojuegos. La información que se recoge se utiliza únicamente para permitir el funcionamiento de la cuenta y de las funciones principales de la aplicación.</p>

            <section>
                <h2>Información que se recoge</h2>
                <p>Al crear una cuenta se guarda el nombre, nick, email y contraseña cifrada. También se pueden guardar datos de perfil como biografía, avatar y encabezado si el usuario decide añadirlos.</p>
                <p>La aplicación almacena la actividad generada dentro de LogNow!: biblioteca personal, estados de juego, puntuaciones, reseñas, listas, favoritos y reportes enviados sobre reseñas.</p>
            </section>

            <section>
                <h2>Uso de la información</h2>
                <p>Los datos se usan para crear y gestionar cuentas, mostrar perfiles públicos, publicar reseñas, mantener la biblioteca personal y permitir las funciones de administración necesarias.</p>
                <p>LogNow! no envía correos publicitarios ni utiliza la información del usuario para campañas comerciales.</p>
            </section>

            <section>
                <h2>Cookies y sesión</h2>
                <p>La aplicación usa cookies técnicas para mantener la sesión iniciada y recordar al usuario mientras navega por la web. Estas cookies son necesarias para poder usar las zonas privadas de la aplicación.</p>
            </section>

            <section>
                <h2>Servicios externos</h2>
                <p>LogNow! utiliza IGDB para obtener información pública sobre videojuegos, como títulos, portadas, plataformas, géneros y fechas de lanzamiento. No se envían datos personales de los usuarios a IGDB para usar la aplicación.</p>
            </section>

            <section>
                <h2>Control de los datos</h2>
                <p>El usuario puede editar su perfil, cambiar su contraseña y gestionar su contenido desde la aplicación. La información personal no se vende ni se cede a terceros.</p>
                <p>Los administradores pueden moderar usuarios, reseñas y reportes cuando sea necesario para mantener el buen funcionamiento de LogNow!.</p>
            </section>
        </div>
    </section>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
