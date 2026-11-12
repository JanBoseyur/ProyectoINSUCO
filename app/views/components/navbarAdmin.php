
<nav class = "navbar">

    <?php if ($usuario['rol'] === 'admin'): ?>

        <div class = "containerSection <?= $seccion === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <a href = "<?= $rutaLayout ?>?seccion=dashboard">Inicio</a>
        </div>

        <div class = "containerSection <?= $seccion === 'a' ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <a href = "<?= $rutaLayout ?>?seccion=a">2</a>
        </div>

        <div class = "containerSection <?= $seccion === 'b' ? 'active' : '' ?>">
            <i data-lucide="ticket"></i>
            <a href = "<?= $rutaLayout ?>?seccion=b">3</a>
        </div>

        <div class = "containerSection <?= $seccion === 'c' ? 'active' : '' ?>">
            <i data-lucide="settings"></i>
            <a href = "<?= $rutaLayout ?>?seccion=c">4</a>
        </div>

    <?php endif; ?>

    <a href = "/app/logout.php" class = "navbarBottom">
        Cerrar Sesión
    </a>

</nav>
