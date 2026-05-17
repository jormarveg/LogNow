<?php
require '../includes/auth.php';
require '../includes/upload.php';

if (!estaLogueado()) {
    header('Location: /login.php');
    exit;
}

$idUsuario = (int) getUsuario()['id'];
$datosUsuario = $usuarioModel->obtenerPorId($idUsuario);

if (!$datosUsuario) {
    header('Location: /logout.php');
    exit;
}

$errorPerfil = '';
$errorPassword = '';
$nombre = trim((string) ($datosUsuario['nombre'] ?? ''));
$nick = trim((string) ($datosUsuario['nick'] ?? ''));
$biografia = trim((string) ($datosUsuario['biografia'] ?? ''));
$avatarActual = trim((string) ($datosUsuario['avatar'] ?? ''));
$encabezadoActual = trim((string) ($datosUsuario['encabezado'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'perfil';

    if ($accion === 'password') {
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordNueva2 = $_POST['password_nueva2'] ?? '';

        if ($passwordActual === '' || $passwordNueva === '' || $passwordNueva2 === '') {
            $errorPassword = 'Todos los campos de contraseña son obligatorios';
        } elseif (!password_verify($passwordActual, $datosUsuario['password'])) {
            $errorPassword = 'La contraseña actual no es correcta';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $passwordNueva)) {
            $errorPassword = 'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula y un número';
        } elseif ($passwordNueva !== $passwordNueva2) {
            $errorPassword = 'Las contraseñas no coinciden';
        } else {
            $usuarioModel->actualizarPassword($idUsuario, $passwordNueva);
            header('Location: /perfil.php?password=ok');
            exit;
        }
    } else {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $nick = trim((string) ($_POST['nick'] ?? ''));
        $biografia = trim((string) ($_POST['biografia'] ?? ''));

        if ($nombre === '' || $nick === '') {
            $errorPerfil = 'El nombre y el nick son obligatorios';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $nick)) {
            $errorPerfil = 'El nick debe tener entre 3 y 20 caracteres (letras, números y _)';
        } elseif (mb_strlen($biografia, 'UTF-8') > 280) {
            $errorPerfil = 'La bio no puede superar los 280 caracteres';
        } elseif ($usuarioModel->existeNickDeOtroUsuario($idUsuario, $nick)) {
            $errorPerfil = 'Ese nick ya está en uso';
        } else {
            $subidaAvatar = subirAvatar($_FILES['avatar'] ?? null, $avatarActual);
            $subidaEncabezado = subirEncabezado($_FILES['encabezado'] ?? null, $encabezadoActual);

            if (!$subidaAvatar['ok']) {
                $errorPerfil = $subidaAvatar['error'];
            } elseif (!$subidaEncabezado['ok']) {
                $errorPerfil = $subidaEncabezado['error'];
            } else {
                $avatarActual = $subidaAvatar['ruta'];
                $encabezadoActual = $subidaEncabezado['ruta'];
                $usuarioModel->actualizarPerfil(
                    $idUsuario,
                    $nombre,
                    $nick,
                    $biografia !== '' ? $biografia : null,
                    $avatarActual !== '' ? $avatarActual : null,
                    $encabezadoActual !== '' ? $encabezadoActual : null
                );

                $usuarioActualizado = $usuarioModel->obtenerPorId($idUsuario);
                actualizarSesionUsuario($usuarioActualizado);

                header('Location: /perfil.php?editado=ok');
                exit;
            }
        }
    }
}

$titulo = 'Editar perfil — LogNow!';
$css = ['perfil.css', 'biblioteca.css'];
$js = ['validacion.js'];
$pagina = 'perfil';
require '../includes/header.php';
?>

<main class="container">
    <section class="cabecera-biblioteca cabecera-editar-perfil">
        <div>
            <p class="eyebrow">Tu perfil</p>
            <h1>Editar perfil</h1>
            <p class="texto-cabecera">Actualiza tu nombre visible, tu nick, la bio, el avatar y el encabezado de tu cuenta.</p>
        </div>
        <a class="boton-secundario" href="/perfil.php">Volver al perfil</a>
    </section>

    <div class="bloque-biblioteca bloque-editar-perfil">
        <section class="resumen-juego-biblioteca resumen-perfil-actual">
            <div class="vista-encabezado-actual" style="background-image: url('<?= htmlspecialchars(urlEncabezadoUsuario($encabezadoActual)) ?>');">
                <div class="portada-resumen avatar-resumen">
                    <img src="<?= htmlspecialchars(urlAvatarUsuario($avatarActual)) ?>" alt="Avatar actual de <?= htmlspecialchars($nick) ?>">
                </div>
            </div>
            <div class="datos-resumen">
                <p class="eyebrow">Vista actual</p>
                <h2><?= htmlspecialchars($nombre) ?></h2>
                <p class="subtexto-resumen">@<?= htmlspecialchars($nick) ?></p>
                <?php if ($biografia !== ''): ?>
                    <p class="meta-resumen"><?= nl2br(htmlspecialchars($biografia)) ?></p>
                <?php else: ?>
                    <p class="meta-resumen">Todavía no has escrito ninguna bio.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="formulario-biblioteca">
            <?php if ($errorPerfil): ?>
                <p class="error"><?= htmlspecialchars($errorPerfil) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="form-editar-perfil">
                <input type="hidden" name="accion" value="perfil">
                <div class="grid-formulario">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($nombre) ?>">
                        <span class="msg-error"></span>
                    </div>

                    <div class="campo">
                        <label for="nick">Nick</label>
                        <input type="text" id="nick" name="nick" minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]{3,20}" required value="<?= htmlspecialchars($nick) ?>">
                        <span class="msg-error"></span>
                    </div>

                    <div class="campo campo-ancho">
                        <label for="biografia">Bio</label>
                        <textarea id="biografia" name="biografia" rows="5" maxlength="280" placeholder="Cuéntanos un poco sobre ti"><?= htmlspecialchars($biografia) ?></textarea>
                        <span class="msg-error"></span>
                    </div>

                    <div class="campo campo-ancho">
                        <label for="avatar">Avatar</label>
                        <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp">
                        <p class="ayuda-campo">Formatos permitidos: JPG, PNG o WEBP. Máximo 5 MB.</p>
                    </div>

                    <div class="campo campo-ancho">
                        <label for="encabezado">Encabezado</label>
                        <input type="file" id="encabezado" name="encabezado" accept=".jpg,.jpeg,.png,.webp">
                        <p class="ayuda-campo">Sube una imagen horizontal para la cabecera del perfil. Máximo 5 MB.</p>
                    </div>
                </div>

                <div class="acciones-formulario">
                    <button type="submit">Guardar cambios</button>
                    <a class="boton-secundario" href="/perfil.php">Cancelar</a>
                </div>
            </form>
        </section>

        <section class="formulario-biblioteca formulario-password-perfil">
            <h2>Cambiar contraseña</h2>
            <?php if ($errorPassword): ?>
                <p class="error"><?= htmlspecialchars($errorPassword) ?></p>
            <?php endif; ?>

            <form method="POST" id="form-password-perfil">
                <input type="hidden" name="accion" value="password">
                <div class="grid-formulario">
                    <div class="campo campo-ancho">
                        <label for="password_actual">Contraseña actual</label>
                        <input type="password" id="password_actual" name="password_actual" required>
                        <span class="msg-error"></span>
                    </div>

                    <div class="campo">
                        <label for="password_nueva">Nueva contraseña</label>
                        <input type="password" id="password_nueva" name="password_nueva" required>
                        <span class="msg-error"></span>
                    </div>

                    <div class="campo">
                        <label for="password_nueva2">Repetir nueva contraseña</label>
                        <input type="password" id="password_nueva2" name="password_nueva2" required>
                        <span class="msg-error"></span>
                    </div>
                </div>

                <div class="acciones-formulario">
                    <button type="submit">Guardar contraseña</button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
