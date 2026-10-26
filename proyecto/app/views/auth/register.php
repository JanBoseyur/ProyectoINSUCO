
<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = procesarRegistro();
}

?>

<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset="UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>

<body>

    <h1>Crear cuenta</h1>

    <?php if ($mensaje !== ''): ?>
        <p><?= htmlspecialchars(
            $mensaje,
            ENT_QUOTES,
            'UTF-8'
        ) ?></p>
    <?php endif; ?>

    <form action = "register.php" method = "POST">
        <label for = "nombre">Nombre</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <label for = "correo">Correo electrónico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="150"
            required
        >

        <label for = "contrasena">Contraseña</label>
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            minlength="8"
            required
        >

        <label for = "rol   ">Escoge el rol:</label>
        <select name = "rol" id = "rol">
            <option value = "admin">Admin</option>
            <option value = "funcionario">Funcionario</option>
        </select>

        <button type = "submit">Registrarme</button>
    </form>

    <p><a href = "login.php">Volver al login</a></p>

</body>

</html>
