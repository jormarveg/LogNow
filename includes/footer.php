<footer>
    <div class="container">
        <ul>
            <li><a href="/acerca.php">Acerca de</a></li>
            <li><a href="/privacidad.php">Política de privacidad</a></li>
        </ul>
        <div class="redes-sociales">
            <a href="https://facebook.com"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://instagram.com"><i class="fa-brands fa-square-instagram"></i></a>
            <a href="https://x.com"><i class="fa-brands fa-x-twitter"></i></a>
        </div>
        <div class="copy-footer">
            <span>&copy; <span class="marca-lognow">LogNow!</span></span>
            <span>Hecho por Jorge Martínez Vegara</span>
        </div>
    </div>
</footer>
<?php if (!empty($usarJquery)): ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php endif; ?>
<?php if (isset($jsExterno) && !empty($jsExterno)): ?>
    <?php foreach ($jsExterno as $archivo): ?>
        <script src="<?= htmlspecialchars($archivo) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (isset($js) && !empty($js)): ?>
    <?php foreach ($js as $archivo): ?>
        <?php $rutaJs = __DIR__ . '/../assets/js/' . $archivo; ?>
        <?php $versionJs = file_exists($rutaJs) ? filemtime($rutaJs) : time(); ?>
        <script src="/assets/js/<?= $archivo ?>?v=<?= $versionJs ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
