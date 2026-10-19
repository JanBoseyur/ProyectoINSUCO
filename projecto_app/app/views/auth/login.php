
<?php

session_start();

require_once __DIR__ . '/../../controllers/AuthController.php';

if (isset($_SESSION['usuario'])) {
    header('Location: /app/views/admin/dashboard.php');
    exit;
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
</head>

<body>
    <?php require_once __DIR__ . '/../components/loginCard.php'; ?>

    <script src = "/js/app.js"></script>
</body>

</html>
