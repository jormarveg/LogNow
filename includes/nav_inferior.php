<nav class="nav-inferior">
    <ul>
        <li<?= $pagina === 'inicio' ? ' class="active"' : '' ?>><a href="/"><i class="fa-solid fa-house"></i><span>Inicio</span></a></li>
        <li<?= $pagina === 'buscar' ? ' class="active"' : '' ?>><a href="/catalogo.php"><i class="fa-solid fa-magnifying-glass"></i><span>Buscar</span></a></li>
        <li<?= $pagina === 'catalogo' ? ' class="active"' : '' ?>><a href="/catalogo.php"><i class="fa-solid fa-gamepad"></i><span>Juegos</span></a></li>
        <li<?= $pagina === 'perfil' ? ' class="active"' : '' ?>><a href="/perfil.php"><i class="fa-solid fa-user"></i><span>Perfil</span></a></li>
    </ul>
</nav>
