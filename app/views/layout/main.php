<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte Técnico INSUCO</title>

    <link rel="stylesheet" href="<?= versionCSS('/public/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= versionCSS('/public/components/metricCard.css') ?>">
    <link rel="stylesheet" href="<?= versionCSS('/public/css/misTickets.css') ?>">
    <?php if ($seccion === 'home'): ?>
        <link rel="stylesheet" href="<?= versionCSS('/public/css/home.css') ?>">
        <link rel="stylesheet" href="<?= versionCSS('/public/components/modalNotificacion.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= versionCSS('/public/components/modal.css') ?>">
    <link rel="stylesheet" href="<?= versionCSS('/public/components/modalState.css') ?>">
    <link rel="stylesheet" href="<?= versionCSS('/public/components/modalEliminar.css') ?>">
    <link rel="stylesheet" href="<?= versionCSS('/public/css/navbar.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/public/assets/images/wrench.png">
</head>
<body>
    <?php require $navbar; ?>

    <main class="contenido <?= $rol === 'admin' ? 'adminLayout' : 'funcionarioLayout' ?>">
        <div class="mainContainer">
            <div class="contentContainer">
                <?php require $pagina; ?>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="<?= htmlspecialchars(versionAsset('/public/js/icons.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <script src="<?= htmlspecialchars(versionAsset('/public/js/modalCerrarSesion.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php foreach ($scriptsPagina as $script): ?>
        <script src="<?= htmlspecialchars(versionAsset($script), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
