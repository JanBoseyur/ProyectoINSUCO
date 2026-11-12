
<?php

session_start();

require_once __DIR__ . '/../../controllers/AuthController.php';

if (is_array($usuario)) {

    $rol = $usuario['rol'] ?? null;

    if ($rol === 'admin') {
        header('Location: /app/views/layout/main.php?seccion=dashboard');
        exit;
    }

    if ($rol === 'funcionario') {
        header('Location: /app/views/layout/main.php?seccion=home');
        exit;
    }

}

procesarLogin();

?>

<!DOCTYPE html>

<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">

    <title>Proyecto INSUCO</title>

    <link rel = "stylesheet" href = "/public/css/login.css">
    <link rel = "icon" type = "image/svg+xml" href = "/public/assets/images/wrench.png">
</head>

<body>
    <?php require_once __DIR__ . '/../components/loginCard.php'; ?>

    <script src = "/js/app.js"></script>
</body>

</html>
