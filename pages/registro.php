<?php
require '../includes/auth.php';

$error = '';
$nombre = '';
$nick = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($nombre) || empty($nick) || empty($email) || empty($password) || empty($password2)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $nick)) {
        $error = 'El nick debe tener entre 3 y 20 caracteres (letras, números y _)';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden';
    } else {
        if ($usuarioModel->existeNickOEmail($nick, $email)) {
            $error = 'El nick o el email ya están registrados';
        } else {
            $usuarioModel->registrar($nombre, $nick, $email, $password);
            header('Location: /login.php?registro=ok');
            exit;
        }
    }
}

$titulo = 'Registro — LogNow!';
$css = ['auth.css'];
$pagina = 'registro';
$js = ['validacion.js'];
require '../includes/header.php';
?>

<main class="container">
    <div class="auth-form">
        <h1>Crear cuenta</h1>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" id="form-registro" novalidate>
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= htmlspecialchars($nombre) ?>">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="nick">Nick</label>
                <input type="text" id="nick" name="nick" required minlength="3" maxlength="20"
                       pattern="[a-zA-Z0-9_]{3,20}" value="<?= htmlspecialchars($nick) ?>">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="8">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="password2">Repetir contraseña</label>
                <input type="password" id="password2" name="password2" required>
                <span class="msg-error"></span>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        <p class="alt-link">¿Ya tienes cuenta? <a href="/login.php">Inicia sesión</a></p>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
