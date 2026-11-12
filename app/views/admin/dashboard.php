
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

$rol = is_array($usuario)
    ? ($usuario['rol'] ?? null)
    : null;

// Validar acceso al dashboard.
if (!is_array($usuario) || !in_array($rol, ['admin', 'funcionario'], true)) {
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

$tickets = obtenerTickets();

$ticketsHoy = obtenerTicketsDeHoy();
$ticketsAbiertos = obtenerTicketsAbiertos();
$ticketsEnProceso = obtenerTicketsEnProceso();
$ticketsResueltos = obtenerTicketsResueltos();

$funcionarios = [];

foreach ($tickets as $ticket) {
    $nombreFuncionario = trim($ticket['funcionario'] ?? '');

    if ($nombreFuncionario !== '') {
        $funcionarios[$nombreFuncionario] = $nombreFuncionario;
    }
}

natcasesort($funcionarios);

$estadoFiltro = $_GET['estado'] ?? 'Todos';

$estadosPermitidos = [
    'Todos',
    'Pendiente',
    'En Proceso',
    'Resuelto'
];

if (!in_array($estadoFiltro, $estadosPermitidos, true)) {
    $estadoFiltro = 'Todos';
}

$funcionarioFiltro = trim($_GET['funcionario'] ?? '');
$tickets = array_filter($tickets, function ($ticket) use ($estadoFiltro, $funcionarioFiltro) {

    $coincideEstado = $estadoFiltro === 'Todos' ||
        ($ticket['estado'] ?? '') === $estadoFiltro;

    $coincideFuncionario = $funcionarioFiltro === '' ||
        stripos($ticket['funcionario'] ?? '', $funcionarioFiltro) !== false;

    return $coincideEstado && $coincideFuncionario;
});

?>

<body>
    
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

    <div class = "ticketFilters">

        <select
            name = "estado"
            id = "filtroEstado"
            class = "filterSelect"
        >
            <option value="Todos" <?= $estadoFiltro === 'Todos' ? 'selected' : '' ?>>
                Todos los tickets
            </option>

            <option value="Pendiente" <?= $estadoFiltro === 'Pendiente' ? 'selected' : '' ?>>
                Pendientes
            </option>

            <option value="En Proceso" <?= $estadoFiltro === 'En Proceso' ? 'selected' : '' ?>>
                En proceso
            </option>

            <option value="Resuelto" <?= $estadoFiltro === 'Resuelto' ? 'selected' : '' ?>>
                Resueltos
            </option>

        </select>
    
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

            <tbody class = "contenidoTabla" id = "tablaTickets">

               <?php foreach ($tickets as $ticket): ?>

                    <tr data-estado="<?= htmlspecialchars($ticket['estado']) ?>">
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

                            <button
                                type = "button"
                                class = "btnHistorial2"
                                onclick = "eliminarTicket(<?= (int) $ticket['id'] ?>)"
                                aria-label = "Eliminar ticket"
                                title = "Ver historial"
                            >
                                <i data-lucide = "circle-x" class = "iconDefault2"></i>
                                <i data-lucide = "x" class = "iconHover2"></i>
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

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src = "/public/js/modal.js" defer></script>
    <script src = "/public/js/modalEstado.js" defer></script>
    <script src = "/public/js/modalConfirmacion.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const filtro = document.getElementById('filtroEstado');
            const filas = document.querySelectorAll('#tablaTickets tr');

            filtro.addEventListener('change', function () {

                const estadoSeleccionado = this.value.toLowerCase();

                filas.forEach(fila => {

                    const estadoTicket = fila.dataset.estado.toLowerCase();

                    if (
                        estadoSeleccionado === 'todos' ||
                        estadoTicket === estadoSeleccionado
                    ) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }

                });

            });

        });
    </script>
    
</body>

</html>
