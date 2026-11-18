
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

$tickets = obtenerTicketsFuncionario((int) $usuario['id']);
$nombre = $usuario['nombre'] ?? '';
$ticketsPendientes = 0;
$ticketsEnProceso = 0;
$ticketsResueltos = 0;

foreach ($tickets as $ticket) {
    switch ($ticket['estado'] ?? '') {
        case 'Pendiente':
            $ticketsPendientes++;
            break;
        case 'En proceso':
            $ticketsEnProceso++;
            break;
        case 'Resuelto':
            $ticketsResueltos++;
            break;
    }
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

        error_log('ID AUTOR ANTES DE CAMBIAR: ' . var_export($idAutor, true));
        error_log('USUARIO EN SESION: ' . print_r($_SESSION['usuario'] ?? null, true));

        $cambioRealizado = cambiarEstado(
            $idTicket,
            $estadoNuevo,
            $idAutor,
            $descripcionCambio,
            $nuevoEncargado
        );

        if (!$cambioRealizado) {
            http_response_code(400);
        }

        echo json_encode([
            'success' => $cambioRealizado,
            'changed' => $cambioRealizado,
            'message' => $cambioRealizado
                ? 'Cambios guardados correctamente.'
                : 'No se pudo actualizar el ticket. Revisa el registro de errores de PHP.'
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

$nombre = $usuario['nombre'] ?? '';

?>

<body>
    
    <div class = "welcomeContainer">
        <div class = "titleContainer">
            <i data-lucide = "user-round" class = "iconAnimated"></i>
            <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>
        </div>
    </div>

    <div class = "sectionHeader misTicketsHeader">
        <h1>Mis tickets</h1>
        <p>Consulta y realiza seguimiento de tus solicitudes de soporte técnico.</p>
    </div>

    <div class="filterSelectorContainer" aria-label="Filtrar mis tickets por estado">
        <div class="buttonFilterSelectorContainer">
            <button type="button" class="buttonFilterSelector active" data-filter="todos" aria-pressed="true">
                Todos <span class="filterCount"><?= count($tickets) ?></span>
            </button>
            <button type="button" class="buttonFilterSelector" data-filter="Pendiente" aria-pressed="false">
                Pendientes <span class="filterCount"><?= $ticketsPendientes ?></span>
            </button>
            <button type="button" class="buttonFilterSelector" data-filter="En proceso" aria-pressed="false">
                En proceso <span class="filterCount"><?= $ticketsEnProceso ?></span>
            </button>
            <button type="button" class="buttonFilterSelector" data-filter="Resuelto" aria-pressed="false">
                Resueltos <span class="filterCount"><?= $ticketsResueltos ?></span>
            </button>
        </div>
    </div>

    <div class="misTicketsTableContainer">
        <table class = "ticketTable">
            
            <thead class = "col">
            
                <tr class = "colContainer">
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
                </tr>

            </thead>

            <tbody class = "contenidoTabla" id = "tablaTickets">

               <?php foreach ($tickets as $ticket): ?>

                    <tr data-estado = "<?= htmlspecialchars($ticket['estado']) ?>">

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
                        </td>
                        
                    </tr>

                <?php endforeach; ?>

                <?php
                    require __DIR__ . '/../components/modalState.php';
                ?>

                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan = "4">No hay tickets registrados.</td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>
    </div>

    <script src = "https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="/public/js/filtroEstado.js" defer></script>
    
</body>

</html>
