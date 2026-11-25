
<?php

require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// --------------------------------------------------
// 1. VERIFICAR SI YA EXISTE UNA SESIÓN
// --------------------------------------------------

$usuario = $_SESSION['usuario'] ?? null;

if (is_array($usuario)) {

    $rol = $usuario['rol'] ?? null;

    if ($rol === 'admin') {
        header('Location: /public/index.php?seccion=dashboard');
        exit;
    }

    if ($rol === 'funcionario') {
        header('Location: /public/index.php?seccion=home');
        exit;
    }
}

// --------------------------------------------------
// 2. PROCESAR LOGIN
// --------------------------------------------------

procesarLogin();

?>

<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Proyecto INSUCO</title>

    <link rel = "stylesheet" href = "/public/css/login.css">
    <link rel = "icon" type = "image/png" href = "/public/assets/images/wrench.png">
</head>

<body>

    <?php require_once __DIR__ . '/../components/loginCard.php'; ?>

    <script src="/public/js/app.js" defer></script>

</body>

</html>
