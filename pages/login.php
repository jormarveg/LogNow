<?php
require '../includes/auth.php';

$error = '';
$identificador = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['identificador'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identificador) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        $usuario = $usuarioModel->buscarPorEmail($identificador);

        if ($usuario && password_verify($password, $usuario['password'])) {
            if (!$usuario['activo']) {
                $error = 'Esta cuenta ha sido desactivada';
            } else {
                iniciarSesion($usuario);
                header('Location: /');
                exit;
            }
        } else {
            $error = 'Email o contraseña incorrectos';
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
        <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
            <p class="exito">Cuenta creada correctamente. Ya puedes iniciar sesión.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" id="form-login">
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="identificador" required
                       value="<?= htmlspecialchars($identificador) ?>">
                <span class="msg-error"></span>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
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
