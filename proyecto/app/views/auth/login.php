
<?php

session_start();

require_once __DIR__ . '/../../../config/database.php';

// Si ya inició sesión, redirigir al panel.
if (isset($_SESSION['usuario'])) {
    header('Location: /app/dashboard.php');
    exit;
}

// Procesar el formulario cuando se envía por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo === '' || $contrasena === '') {
        header('Location: /app/views/auth/login.php?error=1');
        exit;
    }

    try {
        $sql = 'SELECT id, nombre, rol, correo, contrasena_hash
                FROM usuario
                WHERE correo = :correo
                LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['correo' => $correo]);

        $usuario = $stmt->fetch();

        if (
            !$usuario ||
            empty($usuario['contrasena_hash']) ||
            !password_verify(
                $contrasena,
                $usuario['contrasena_hash']
            )
        ) {
            header('Location: /app/views/auth/login.php?error=1');
            exit;
        }

        // Renovar el ID de sesión después de autenticar.
        session_regenerate_id(true);

        // Guardar los datos del usuario autenticado.
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol'],
            'correo' => $usuario['correo']
        ];

        // Redirigir según el rol.
        switch ($usuario['rol']) {

            case 'admin':
                header('Location: /app/views/admin/dashboard.php');
                break;

            case 'tecnico':
                header('Location: /app/views/admin/dashboard.php');
                break;

            case 'usuario':
                header('Location: /app/views/admin/dashboard.php');
                break;

            default:
                session_unset();
                session_destroy();

                header('Location: /app/views/auth/login.php?error=1');
                break;
        }

        exit;

    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Error al procesar el inicio de sesión.');
    }
}

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
