
<?php

$titulo = $titulo ?? 'INSUCO';
$mostrarNavbar = $mostrarNavbar ?? true;
$mostrarSidebar = $mostrarSidebar ?? true;
$mostrarFooter = $mostrarFooter ?? true;

?>

<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>

    <link rel = "stylesheet" href = "/components/loginCard.css">
</head>

<body>

    <?php if ($mostrarNavbar): ?>
        <?php require __DIR__ . '/../components/navbar.php'; ?>
    <?php endif; ?>

    <div class="app-container">

        <?php if ($mostrarSidebar): ?>
            <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <?php endif; ?>

        <main class="main-content">
            <?php
            if (isset($vista) && is_file($vista)) {
                require $vista;
            } else {
                echo '<h1>No se encontró la vista solicitada.</h1>';
            }
            ?>
        </main>

    </div>

    <?php if ($mostrarFooter): ?>
        <?php require __DIR__ . '/../components/footer.php'; ?>
    <?php endif; ?>

    <script src="/js/app.js"></script>

</body>
</html>