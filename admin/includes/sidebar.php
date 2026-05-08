<aside class="admin-sidebar">
    <h2>Admin</h2>
    <nav>
        <ul>
            <li<?= $adminPagina === 'inicio' ? ' class="active"' : '' ?>>
                <a href="/admin/">Resumen</a>
            </li>
            <li<?= $adminPagina === 'usuarios' ? ' class="active"' : '' ?>>
                <a href="/admin/usuarios.php">Usuarios</a>
            </li>
            <li<?= $adminPagina === 'reportes' ? ' class="active"' : '' ?>>
                <a href="/admin/reportes.php">Reportes</a>
            </li>
        </ul>
    </nav>
</aside>
