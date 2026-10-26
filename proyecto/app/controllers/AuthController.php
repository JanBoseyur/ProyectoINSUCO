
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

function procesarRegistro(): string
{
    global $pdo;

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Validar los datos recibidos.
    if (
        $nombre === '' ||
        strlen($nombre) > 100 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($correo) > 150 ||
        !is_string($contrasena) ||
        strlen($contrasena) < 8 ||
        strlen($contrasena) > 4096 ||
        !in_array($rol, ['admin', 'tecnico', 'funcionario'], true)
    ) {
        return 'Revisa los datos ingresados. La contraseña debe tener al menos 8 caracteres.';
    }

    try {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO usuario
                (nombre, rol, correo, contrasena_hash)
                VALUES (:nombre, :rol, :correo, :hash)';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'nombre' => $nombre,
            'rol' => $rol,
            'correo' => $correo,
            'hash' => $hash
        ]);

        // Redirigir cuando el registro sea exitoso.
        header('Location: login.php?registro=exitoso');
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return 'No se pudo registrar la cuenta. Comprueba los datos o si el correo ya existe.';
        }

        error_log($e->getMessage());
        http_response_code(500);

        return 'Ocurrió un error al registrar la cuenta.';
    }
}