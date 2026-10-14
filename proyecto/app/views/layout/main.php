
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($titulo ?? 'INSUCO') ?></title>

    <link rel = "stylesheet" href = "/css/style.css">
</head>

<body>

    <?php if (!empty($mostrarNavbar)): ?>
        <?php require __DIR__ . '/../components/navbar.php'; ?>
    <?php endif; ?>

    <main>
        <?php
        if (isset($vista) && is_file($vista)) {
            require $vista;
        }
        ?>
    </main>

    <script src="/js/app.js"></script>
</body>

</html>