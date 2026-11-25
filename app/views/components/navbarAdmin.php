
<nav class = "navbar navbarAdmin">

    <?php if ($usuario['rol'] === 'admin'): ?>

        <div class = "containerSection <?= $seccion === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide = "home"></i>
            <a href = "<?= $rutaLayout ?>?seccion=dashboard">Inicio</a>
        </div>

        <div class = "containerSection <?= $seccion === 'configuracion' ? 'active' : '' ?>">
            <i data-lucide = "settings"></i>
            <a href = "<?= $rutaLayout ?>?seccion=configuracion">Administración</a>
        </div>

    <?php endif; ?>

    <a href = "/app/logout.php" class = "navbarBottom" data-confirm-logout>
        Cerrar Sesión
    </a>

</nav>

<nav class="mobileNavbar" aria-label="Navegación principal">
    <a
        class="<?= $seccion === 'dashboard' ? 'active' : '' ?>"
        href="<?= $rutaLayout ?>?seccion=dashboard"
    >
        <i data-lucide="home" aria-hidden="true"></i>
        <span>Inicio</span>
    </a>

    <a
        class="<?= $seccion === 'configuracion' ? 'active' : '' ?>"
        href="<?= $rutaLayout ?>?seccion=configuracion"
    >
        <i data-lucide="settings" aria-hidden="true"></i>
        <span>Administración</span>
    </a>

    <a href="/app/logout.php" data-confirm-logout>
        <i data-lucide="log-out" aria-hidden="true"></i>
        <span>Salir</span>
    </a>
</nav>
