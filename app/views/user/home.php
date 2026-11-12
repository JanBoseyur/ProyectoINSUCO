
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

$rol = is_array($usuario)
    ? ($usuario['rol'] ?? null)
    : null;

// Validar acceso al dashboard.
if (!is_array($usuario) || !in_array($rol, ['funcionario'], true)) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $usuario['nombre'] ?? '';
$mensaje = '';
$tipoMensaje = '';

if (empty($_SESSION['csrf_ticket'])) {
    $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_ticket'], $token)) {
        $mensaje = 'No se pudo validar el formulario.';
        $tipoMensaje = 'error';

    } else {
        
        try {
            $lugar = trim($_POST['lugar'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            $idTicket = registrarTicket(
                (int) $usuario['id'],
                $lugar,
                $descripcion
            );

            $mensaje = '¡Ticket creado correctamente!';
            $tipoMensaje = 'exito';

            $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));

        } catch (Throwable $e) {

            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';

        }
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel = "stylesheet" href = "/public/css/home.css">
</head>

<body>

<div class = "ticket-container">

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje-ticket <?= $tipoMensaje ?>">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form class = "formularioContainer" method="POST" action="">

        <div>
            <h2>Crear nuevo ticket</h3>
            <p>Describe el problema que necesitas solucionar.</p>
        </div>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_ticket']) ?>"
        >

        <div class = "form-group">
            <label>Solicitante:</label>

            <input
                type="text"
                value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                disabled
            >
        </div>
        
        <div class = "form-group">
            <label for="lugar">Lugar:</label>

            <input
                type="text"
                id="lugar"
                name="lugar"
                placeholder="Ej: Laboratorio de computación"
                maxlength="150"
                required
            >
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción del problema</label>

            <textarea
                id="descripcion"
                name="descripcion"
                rows="5"
                maxlength="1000"
                placeholder="Describe el problema..."
                required
            ></textarea>
        </div>

        <button type = "submit" name = "crear_ticket">
            Enviar ticket
        </button>

    </form>

</div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src = "/public/js/modal.js" defer></script>
    <script src = "/public/js/modalEstado.js" defer></script>
    
</body>

</html>