
<?php

require_once __DIR__ . '/../models/User.php';

function procesarLogin(): void

{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo === '' || $contrasena === '') {
        header('Location: /app/views/auth/login.php?error=1');
        exit;
    }

    $usuario = buscarUsuarioPorCorreo($correo);

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

    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'rol' => $usuario['rol'],
        'correo' => $usuario['correo']
    ];

    switch ($usuario['rol']) {
        case 'admin':
            header('Location: /app/views/admin/dashboard.php');
            break;

        case 'tecnico':
            header('Location: /a    pp/views/admin/dashboard.php');
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
}
