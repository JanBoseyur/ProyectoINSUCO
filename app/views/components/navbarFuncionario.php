
<nav class = "navbar">

    <?php if ($usuario['rol'] === 'funcionario'): ?>

        <div class = "containerSection <?= $seccion === 'home' ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <a href = "<?= $rutaLayout ?>?seccion=home">Inicio</a>
        </div>

        <div class = "containerSection <?= $seccion === 'misTickets' ? 'active' : '' ?>">
            <i data-lucide="ticket"></i>
            <a href = "<?= $rutaLayout ?>?seccion=misTickets">Mis Tickets</a>
        </div>

    <?php elseif ($usuario['rol'] === 'funcionario'): ?>

        <div class = "containerSection <?= $seccion === 'home' ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <a href = "<?= $rutaLayout ?>?seccion=home">Inicio</a>
        </div>

        <div class = "containerSection <?= $seccion === 'misTickets' ? 'active' : '' ?>">
            <i data-lucide="ticket"></i>
            <a href = "<?= $rutaLayout ?>?seccion=misTickets">Crear ticket</a>
        </div>

    <?php endif; ?>

    <a href = "/app/logout.php" class = "navbarBottom">
        Cerrar Sesión
    </a>

</nav>

