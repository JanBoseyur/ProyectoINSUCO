<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

if (!is_array($usuario) || ($usuario['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $usuario['nombre'] ?? '';
$tickets = obtenerTickets();

$ticketsHoy = obtenerTicketsDeHoy();
$ticketsTodos = count($tickets);
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

$cantidadPendientes = count(array_filter($tickets, static function ($ticket) {
    return ($ticket['estado'] ?? '') === 'Pendiente';
}));

$estadosPermitidos = ['Todos', 'Pendiente', 'En proceso', 'Resuelto'];
$estadoFiltro = trim((string) ($_GET['estado'] ?? 'Todos'));
if (!in_array($estadoFiltro, $estadosPermitidos, true)) {
    $estadoFiltro = 'Todos';
}

$funcionarioFiltro = trim((string) ($_GET['funcionario'] ?? ''));
$tickets = array_filter($tickets, static function ($ticket) use ($estadoFiltro, $funcionarioFiltro) {
    $coincideEstado = $estadoFiltro === 'Todos' ||
        ($ticket['estado'] ?? '') === $estadoFiltro;
    $coincideFuncionario = $funcionarioFiltro === '' ||
        stripos($ticket['funcionario'] ?? '', $funcionarioFiltro) !== false;

    return $coincideEstado && $coincideFuncionario;
});

?>

<body>
    
    <div class = "welcomeContainer">
        <div class = "titleContainer">
            <i data-lucide = "user-round" class = "iconAnimated"></i>
            <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>
        </div>
    </div>

    <div class = "sectionHeader misTicketsHeader">
        <h1>Dashboard</h1>
        <p>Aquí puedes supervisar las solicitudes de soporte técnico y el estado de atención de la institución.</p>
    </div>

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

<!--     <div class = "ticketFilters">

        <input
            type = "search"
            class = "buscadorTicket"
            id = "buscadorTicket"
            placeholder = "Buscar tickets..."
            autocomplete = "off"
        >

        <select
            name = "estado"
            id = "filtroEstado"
            class = "filterSelect"
        >
            <option value = "Todos" <?= $estadoFiltro === 'Todos' ? 'selected' : '' ?>>
                Todos
            </option>

            <option value = "Pendiente" <?= $estadoFiltro === 'Pendiente' ? 'selected' : '' ?>>
                Pendientes
            </option>

            <option value = "En proceso" <?= $estadoFiltro === 'En proceso' ? 'selected' : '' ?>>
                En proceso
            </option>

            <option value = "Resuelto" <?= $estadoFiltro === 'Resuelto' ? 'selected' : '' ?>>
                Resueltos
            </option>

        </select>

    </div> -->

    <div class = "filterSelectorContainer">

        <div class = "buttonFilterSelectorContainer">

            <button type="button"
                class="buttonFilterSelector active"
                data-filter="todos"
                aria-pressed="true">
                Todos
                <span class="filterCount"><?= $ticketsTodos ?></span>
            </button>

            <button type="button"
                class="buttonFilterSelector"
                data-filter="Pendiente"
                aria-pressed="false">
                Pendientes
                <span class="filterCount"><?= $cantidadPendientes ?></span>
            </button>

            <button type="button"
                class="buttonFilterSelector"
                data-filter="En proceso"
                aria-pressed="false">
                En proceso
                <span class="filterCount"><?= $ticketsEnProceso ?></span>
            </button>

            <button type="button"
                class="buttonFilterSelector"
                data-filter="Resuelto"
                aria-pressed="false">
                Cerrados
                <span class="filterCount"><?= $ticketsResueltos ?></span>
            </button>

        </div>

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

                    <tr data-estado = "<?= htmlspecialchars($ticket['estado']) ?>">
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
                        </td>

                        <td class = "iconContainer">
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
    <script src = "/public/js/filtroEstado.js" defer></script>
    
</body>
