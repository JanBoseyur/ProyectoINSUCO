
<?php

require_once __DIR__ . '/config/database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $nombre === '' ||
        strlen($nombre) > 100 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 150 ||
        strlen($password) < 8 ||
        strlen($password) > 4096
    ) {
        $mensaje = 'Revisa los datos ingresados. La contraseña debe tener al menos 8 caracteres.';
    } else {
        try {
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = 'INSERT INTO usuario
                    (nombre, correo, password_hash)
                    VALUES (:nombre, :correo, :hash)';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'hash' => $hash
            ]);

            header('Location: main.php?registro=exitoso');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $mensaje = 'No se pudo registrar la cuenta. Comprueba los datos o si el correo ya existe.';
            } else {
                error_log($e->getMessage());
                http_response_code(500);
                $mensaje = 'Ocurrió un error al registrar la cuenta.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <form action="registro.php" method="POST">
        <label for="nombre">Nombre</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <label for="correo">Correo electrónico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="150"
            required
        >

        <label for="password">Contraseña</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <button type="submit">Registrarme</button>
    </form>

    <p><a href="main.php">Volver al login</a></p>

</body>
</html>
