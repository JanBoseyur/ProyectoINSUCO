

<?php

session_start();

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;
$rol = is_array($usuario) ? ($usuario['rol'] ?? null) : null;

// Validar acceso al dashboard.
if (!is_array($usuario) || !in_array($rol, ['admin'], true)) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

// Procesar solicitudes para cambiar el estado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (
        !is_array($datos) ||
        ($datos['accion'] ?? '') !== 'cambiar_estado'
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Solicitud no válida.'
        ]);

        exit;
    }

    try {
        $idTicket = filter_var(
            $datos['id_ticket'] ?? null,
            FILTER_VALIDATE_INT
        );

        $estadoNuevo = trim(
            (string) ($datos['estado'] ?? '')
        );

        // Obtener los campos opcionales.
        $descripcionCambio = trim(
            (string) ($datos['descripcion'] ?? '')
        );

        $encargadoRecibido = trim(
            (string) ($datos['encargado'] ?? '')
        );

        // Si está vacío, se mantiene el encargado actual.
        $nuevoEncargado = $encargadoRecibido !== ''
            ? $encargadoRecibido
            : null;

        $idAutor = (int) ($usuario['id'] ?? 0);

        // Validaciones.
        if (!$idTicket || $idAutor <= 0) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'El ticket o el usuario no son válidos.'
            ]);

            exit;
        }

        if (mb_strlen($descripcionCambio) > 1000) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'La descripción no puede superar los 1000 caracteres.'
            ]);

            exit;
        }

        if (
            $nuevoEncargado !== null &&
            mb_strlen($nuevoEncargado) > 150
        ) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'El encargado no puede superar los 150 caracteres.'
            ]);

            exit;
        }

        // Ejecutar el cambio.
        $cambioRealizado = cambiarEstado(
            $idTicket,
            $estadoNuevo,
            $idAutor,
            $descripcionCambio,
            $nuevoEncargado
        );

        echo json_encode([
            'success' => true,
            'changed' => $cambioRealizado,
            'message' => $cambioRealizado
                ? 'Cambios guardados correctamente.'
                : 'El ticket no existe o no hubo cambios.'
        ]);

    } catch (Throwable $e) {
        error_log(
            'Error al cambiar estado: ' . $e->getMessage()
        );

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron guardar los cambios.'
        ]);
    }

    exit;
}

// Cargar datos para mostrar el dashboard.
$nombre = $usuario['nombre'] ?? '';

$tickets = obtenerTickets();
$ticketsHoy = obtenerTicketsDeHoy();
$ticketsAbiertos = obtenerTicketsAbiertos();
$ticketsEnProceso = obtenerTicketsEnProceso();
$ticketsResueltos = obtenerTicketsResueltos();

?>

<!DOCTYPE html>

<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Dashboard</title>

    <link rel = "stylesheet" href = "/public/css/dashboard.css">
    <link rel = "stylesheet" href = "/public/components/metricCard.css">
    <link rel = "stylesheet" href = "/public/css/navbar.css">
    <link rel = "icon" type = "image/svg+xml" href = "/public/assets/images/wrench.png">
</head>

<body>

    <div class = "mainContainer">

        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <div class = "contentContainer">

            <div class = "titleContainer">
                <i data-lucide = "user-round"></i>
                <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>
            </div>

            <text class = "subtitle">Aquí puedes supervisar las solicitudes de soporte técnico y el estado de atención de la institución</text>

            <div class = "graphsContainer">

                <?php
                    $titulo = 'Tickets de Hoy';
                    $subtitulo = 'Tickets registrados el día de hoy';
                    $icono = 'ticket';
                    $valor = $ticketsHoy;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

                <?php
                    $titulo = 'Tickets Abiertos';
                    $subtitulo = 'Pendientes de resolución';
                    $icono = 'clock';
                    $valor = $ticketsAbiertos;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

                <?php
                    $titulo = 'Tickets en Proceso';
                    $subtitulo = 'Solicitudes que están siendo atendidas';
                    $icono = 'wrench';
                    $valor = $ticketsEnProceso;

                    include __DIR__ . '/../components/metricCard.php';
                ?>
                
                <?php
                    $titulo = 'Tickets Resueltos';
                    $subtitulo = 'Solicitudes correctamente terminadas';
                    $icono = 'circle-check-big';
                    $valor = $ticketsResueltos;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

            </div>

            <div class = "ticketContainer">

                <table class = "ticketTable">
                    
                    <thead class = "col">
                    
                        <tr class = "colContainer">
                            <th>
                                <div class = "headerLabel">             
                                    <span>ID</span>
                                </div>
                            </th>

                            <th>
                                <div class = "headerLabel">
                                    <span>Funcionario</span>
                                </div>
                            </th>

                            <th>
                                <div class = "headerLabel">
                                    <span>Ubicación</span>
                                </div>
                            </th>
                        
                            <th>
                                <div class = "headerLabel">
                                    <span>Descripción</span>
                                </div>
                            </th>

                            <th>
                                <div class = "headerLabel">
                                    <span>Estado</span>
                                </div>
                            </th>

                            <th>
                                <div class = "headerLabel">
                                    <span></span>
                                </div>
                            </th>
                        </tr>

                    </thead>

                    <tbody class = "contenidoTabla">

                        <?php foreach ($tickets as $ticket): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) $ticket['id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['funcionario'] ?? '') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['lugar']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['descripcion'] ?? '') ?>
                                </td>

                                <td class = "estadoContainer">
                                    <span class = "state <?= htmlspecialchars(str_replace(' ', '-', strtolower(trim($ticket['estado'] ?? '')))) ?>">
                                        <?= htmlspecialchars($ticket['estado'] ?? '') ?>
                                    </span>

                                    <button
                                        type = "button"
                                        class = "btnHistorial"
                                        onclick = "abrirModalEstado(<?= (int) $ticket['id'] ?>, '<?= htmlspecialchars($ticket['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>')"
                                        title = "Cambiar estado"
                                        aria-label = "Cambiar estado del ticket <?= (int) $ticket['id'] ?>"
                                    >
                                        <i data-lucide = "refresh-cw" class = "iconDefault"></i>
                                        <i data-lucide = "refresh-ccw" class = "iconHover"></i>
                                    </button>
                                </td>

                                <td>
                                    <button
                                        type = "button"
                                        class = "btnHistorial"
                                        onclick = "abrirHistorial(<?= (int) $ticket['id'] ?>)"
                                        aria-label = "Ver historial del ticket"
                                        title = "Ver historial"
                                    >
                                        <i data-lucide = "eye-closed" class = "iconDefault"></i>
                                        <i data-lucide = "eye" class = "iconHover"></i>
                                    </button>
                                </td>
                                
                            </tr>

                        <?php endforeach; ?>

                        <?php
                                require __DIR__ . '/../components/modalState.php';
                        ?>

                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan = "6">No hay tickets registrados.</td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        <?php
            $tituloModal = 'Historial del ticket';
            $idModal = 'modalHistorial';
            $anchoModal = '1000px';

            ob_start();

            ?>

            <div id = "contenidoHistorial">
                <p>Selecciona un ticket para consultar su historial.</p>
            </div>

            <?php
                $contenidoModal = ob_get_clean();
                require __DIR__ . '/../components/modal.php';
        ?>

        </div>
        
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src = "/public/js/modal.js" defer></script>
    <script src = "/public/js/modalEstado.js" defer></script>
    
</body>

</html>
