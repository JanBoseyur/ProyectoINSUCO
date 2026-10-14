
<?php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['rol'] !== 'admin'
) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];

?>

<!DOCTYPE html>

<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <link rel = "stylesheet" href = "/css/login.css">
</head>

<body>
    <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>

    <a href = "/app/logout.php">Cerrar sesión</a>

    <script src = "/js/app.js"></script>
</body>

</html>
