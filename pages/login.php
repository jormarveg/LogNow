<?php
require '../includes/auth.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? ($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        $usuario = $usuarioModel->buscarPorEmailONick($login);

        if ($usuario && password_verify($password, $usuario['password'])) {
            if (!$usuario['activo']) {
                $error = 'Esta cuenta ha sido desactivada';
            } else {
                iniciarSesion($usuario);
                header('Location: /');
                exit;
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}

$titulo = 'Iniciar sesión — LogNow!';
$css = ['auth.css'];
$pagina = 'login';
$js = ['validacion.js'];
require '../includes/header.php';
?>

<main class="container">
    <div class="auth-form">
        <h1>Iniciar sesión</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
            <p class="exito">Cuenta creada correctamente.</p>
        <?php endif; ?>
        <?php if (isset($_GET['cuenta']) && $_GET['cuenta'] === 'desactivada'): ?>
            <p class="error">Esta cuenta ha sido desactivada.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" id="form-login">
            <div class="campo">
                <label for="login">Email o nick<span class="asterisco-obligatorio">*</span></label>
                <input type="text" id="login" name="login" autocomplete="username" required
                       value="<?= htmlspecialchars($login) ?>">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="password">Contraseña<span class="asterisco-obligatorio">*</span></label>
                <input type="password" id="password" name="password" required>
                <span class="msg-error"></span>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p class="alt-link">¿No tienes cuenta? <a href="/registro.php">Regístrate</a></p>
    </div>
</main>

<?php
require '../includes/nav_inferior.php';
require '../includes/footer.php';
?>
