
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

$lugar = '';
$descripcion = '';
$categoria = '';
$lugares = obtenerLugaresActivos();
$categorias = obtenerCategoriasActivas();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['añadir_categoria'])) {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_ticket'], $token)) {
        $mensaje = 'No se pudo validar el formulario.';
        $tipoMensaje = 'error';
        
    } else {
        
        try {
            $nombreCategoria = trim($_POST['nombre_categoria'] ?? '');

            if ($nombreCategoria === '' || mb_strlen($nombreCategoria) > 100) {
                throw new InvalidArgumentException('Ingresa un nombre de categoría de hasta 100 caracteres.');
            }

            agregarCategoria($nombreCategoria);
            $categorias = obtenerCategoriasActivas();
            $mensaje = 'Categoría agregada correctamente.';
            $tipoMensaje = 'categoria_exito';
            $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }
    }
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
            $categoria = trim($_POST['categoria'] ?? '');

            $idTicket = registrarTicket(
                (int) $usuario['id'],
                $lugar,
                $descripcion,
                $categoria
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

$ticketsUsuario = obtenerTicketsFuncionario((int) ($usuario['id'] ?? 0));
$ticketsPendientes = 0;
$ticketsEnProcesoUsuario = 0;
$ticketsResueltosUsuario = 0;

foreach ($ticketsUsuario as $ticketUsuario) {
    switch ($ticketUsuario['estado'] ?? '') {
        
        case 'Pendiente':
            $ticketsPendientes++;
            break;
        case 'En proceso':
            $ticketsEnProcesoUsuario++;
            break;
        case 'Resuelto':
            $ticketsResueltosUsuario++;
            break;
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Home</title>

    <link rel = "stylesheet" href = "/public/css/home.css">
    <link rel = "stylesheet" href = "/public/components/modalNotificacion.css">
</head>

<body>

    <div class = "titleContainer">
        <i data-lucide = "user-round"></i>
        <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>
    </div>

    <div class = "ticket-container">

        <?php if ($tipoMensaje === 'error'): ?>
            <p class="ticket-form-error" role="alert">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php elseif ($tipoMensaje === 'categoria_exito'): ?>
            <p role="status">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form class = "formularioContainer" method = "POST" action ="">

            <div class = "sectionHeader misTicketsHeader">
                <h1>Crear nuevo ticket</h1>
                <p>Describe el problema que necesitas solucionar.</p>
            </div>

            <input
                type = "hidden"
                name = "csrf_token"
                value = "<?= htmlspecialchars($_SESSION['csrf_ticket']) ?>"
            >
            
            <div class = "selects-container">
                <div class = "form-group">
                    <label for = "lugar">Lugar:</label>

                    <select name = "lugar" id = "lugar" class = "filterSelect" required>
                        <option value="">Seleccionar lugar</option>

                        <?php foreach ($lugares as $opcionLugar): ?>
                            <option
                                value="<?= (int) $opcionLugar['id'] ?>"
                                <?= ((string)($_POST['lugar'] ?? '') === (string)$opcionLugar['id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($opcionLugar['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class = "form-group">
                    <label for = "categoria">Categoría:</label>
                    
                    <select name = "categoria" id = "categoria" class = "filterSelect" required>
                        <option value = "">Seleccionar categoría</option>
                        
                            <?php foreach ($categorias as $opcionCategoria): ?>
                                <option
                                    value="<?= htmlspecialchars($opcionCategoria['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    <?= ((string)($_POST['categoria'] ?? '') === (string)$opcionCategoria['nombre']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($opcionCategoria['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>

                    </select>
                </div>

            </div>

            <div class = "form-group">
                <label for = "descripcion">Descripción del problema</label>

                <textarea
                    id = "descripcion"
                    name = "descripcion"
                    rows = "5"
                    maxlength = "1000"
                    placeholder = "Describe el problema..."
                    required
                ></textarea>
            </div>

            <button type = "submit" name = "crear_ticket">
                Enviar ticket
            </button>

        </form>

    </div>

    <?php require __DIR__ . '/../components/modalNotificacion.php'; ?>

    <script src = "https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src =  "/public/js/modalNotificacion.js"></script>
    <script src = "/public/js/filtroEstado.js" defer></script>

    <?php if ($tipoMensaje === 'exito'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                mostrarModalExito(
                    '¡Ticket creado con éxito!',
                    'El ticket ha sido registrado correctamente en el sistema.'
                );
            });
        </script>
    <?php endif;?>
    
</body>

</html>
