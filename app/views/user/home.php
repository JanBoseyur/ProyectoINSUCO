
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

$lugar = '';
$descripcion = '';
$lugares = obtenerLugaresActivos();

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

        <form class = "formularioContainer" method="POST" action="">

            <div>
                <h2>Crear nuevo ticket</h2>
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
            
            <div class="form-group">
                <label for="lugar">Lugar:</label>

                <select name="lugar" id="lugar" class="filterSelect" required>
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
                <label for = "descripcion">Descripción del problema</label>

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

    <section class="homeTicketsSection" aria-labelledby="misSolicitudesTitulo">
        <div class="homeTicketsHeader">
            <div>
                <h2 id="misSolicitudesTitulo">Mis solicitudes</h2>
                <p>Revisa el estado de tus tickets.</p>
            </div>
        </div>

        <div class="filterSelectorContainer" aria-label="Filtrar tickets por estado">
            <div class="buttonFilterSelectorContainer">
                <button type="button" class="buttonFilterSelector active" data-filter="todos" aria-pressed="true">
                    Todos <span class="filterCount"><?= count($ticketsUsuario) ?></span>
                </button>
                <button type="button" class="buttonFilterSelector" data-filter="Pendiente" aria-pressed="false">
                    Pendientes <span class="filterCount"><?= $ticketsPendientes ?></span>
                </button>
                <button type="button" class="buttonFilterSelector" data-filter="En proceso" aria-pressed="false">
                    En proceso <span class="filterCount"><?= $ticketsEnProcesoUsuario ?></span>
                </button>
                <button type="button" class="buttonFilterSelector" data-filter="Resuelto" aria-pressed="false">
                    Resueltos <span class="filterCount"><?= $ticketsResueltosUsuario ?></span>
                </button>
            </div>
        </div>

        <div class="ticketContainer homeTicketsContainer">
            <table class="ticketTable">
                <thead>
                    <tr>
                        <th><div class="headerLabel"><span>ID</span></div></th>
                        <th><div class="headerLabel"><span>Ubicación</span></div></th>
                        <th><div class="headerLabel"><span>Descripción</span></div></th>
                        <th><div class="headerLabel"><span>Estado</span></div></th>
                    </tr>
                </thead>
                <tbody id="tablaTickets" class="contenidoTabla">
                    <?php foreach ($ticketsUsuario as $ticketUsuario): ?>
                        <tr data-estado="<?= htmlspecialchars($ticketUsuario['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= (int) $ticketUsuario['id'] ?></td>
                            <td><?= htmlspecialchars($ticketUsuario['lugar'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($ticketUsuario['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="estadoContainer">
                                <span class="state <?= htmlspecialchars(str_replace(' ', '-', strtolower(trim($ticketUsuario['estado'] ?? ''))), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($ticketUsuario['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ticketsUsuario)): ?>
                        <tr class="homeTicketsEmpty">
                            <td colspan="4">Todavía no has creado tickets.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script src = "https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src =  "/public/js/modalNotificacion.js"></script>
    <script src="/public/js/filtroEstado.js" defer></script>

    <?php if ($tipoMensaje === 'exito'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                mostrarModalExito(
                    '¡Ticket creado con éxito!',
                    'El ticket ha sido registrado correctamente en el sistema.'
                );
            });
        </script>
    <?php endif; ?>
    
</body>

</html>
