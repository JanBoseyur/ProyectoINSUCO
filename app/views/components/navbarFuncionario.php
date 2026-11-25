
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

    <a href = "/app/logout.php" class = "navbarBottom" data-confirm-logout>
        Cerrar Sesión
    </a>

</nav>

<nav class="mobileNavbar" aria-label="Navegación principal">
    <a
        class="<?= $seccion === 'home' ? 'active' : '' ?>"
        href="<?= $rutaLayout ?>?seccion=home"
    >
        <i data-lucide="home" aria-hidden="true"></i>
        <span>Inicio</span>
    </a>

    <a
        class="<?= $seccion === 'misTickets' ? 'active' : '' ?>"
        href="<?= $rutaLayout ?>?seccion=misTickets"
    >
        <i data-lucide="ticket" aria-hidden="true"></i>
        <span>Mis tickets</span>
    </a>

    <a href="/app/logout.php" data-confirm-logout>
        <i data-lucide="log-out" aria-hidden="true"></i>
        <span>Salir</span>
    </a>
</nav>

