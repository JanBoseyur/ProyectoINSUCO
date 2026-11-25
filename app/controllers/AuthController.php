
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

    // Validar roles autorizados.
    if (!in_array($usuario['rol'], ['admin', 'funcionario'], true)) {
        header('Location: /app/views/auth/login.php?error=1');
        exit;
    }

    // Regenerar ID de sesión.
    session_regenerate_id(true);

    // Crear sesión única para ambos roles.
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'rol' => $usuario['rol'],
        'correo' => $usuario['correo']
    ];

    // Ambos utilizan el mismo layout.
    $seccionInicial = $usuario['rol'] === 'admin'
        ? 'dashboard'
        : 'home';

    header(
        'Location: /app/views/layout/main.php?seccion=' . $seccionInicial
    );

    exit;
}

function procesarCambioContrasena(): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        return 'La solicitud venció. Recarga la página e inténtalo nuevamente.';
    }

    $correo = $_POST['correo'] ?? '';
    $nueva = $_POST['contrasena_nueva'] ?? '';

    if (!is_string($correo) || !is_string($nueva)) {
        return 'Completa los datos con un correo válido.';
    }

    $correo = trim($correo);
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return 'Completa los datos con un correo válido.';
    }

    $usuario = buscarUsuarioPorCorreo($correo);
    if (!$usuario) {
        return 'No existe una cuenta asociada a ese correo.';
    }

    if (strlen($nueva) < 8 || strlen($nueva) > 4096) {
        return 'La nueva contraseña debe tener al menos 8 caracteres.';
    }

    if (!actualizarContrasena((int) $usuario['id'], password_hash($nueva, PASSWORD_DEFAULT))) {
        return 'No fue posible actualizar la contraseña. Inténtalo nuevamente.';
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Location: /app/views/auth/login.php?contrasena=actualizada');
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
