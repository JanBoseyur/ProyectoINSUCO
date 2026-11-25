
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;

if (!is_array($usuario) || ($usuario['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $usuario['nombre'] ?? '';
$tickets = obtenerTickets();
$categorias = obtenerCategoriasActivas();
$mensaje = '';
$tipoMensaje = '';

function fechaTicketEnEspanol(string $fecha): string
{
    $momento = new DateTimeImmutable($fecha);
    $dias = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
    ];
    
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    return sprintf(
        '%s %d de %s a las %s hrs',
        $dias[(int) $momento->format('N')],
        (int) $momento->format('j'),
        $meses[(int) $momento->format('n')],
        $momento->format('H:i')
    );
}

$ticketsHoy = obtenerTicketsDeHoy();
$ticketsTodos = count($tickets);
$ticketsAbiertos = obtenerTicketsAbiertos();
$ticketsEnProceso = obtenerTicketsEnProceso();
$ticketsResueltos = obtenerTicketsResueltos();

$cantidadPendientes = count(array_filter($tickets, static function ($ticket) {
    return ($ticket['estado'] ?? '') === 'Pendiente';
}));

if (empty($_SESSION['csrf_ticket'])) {
    $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (isset($_POST['añadir_categoria']) || isset($_POST['eliminar_categoria']))
) {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_ticket'], $token)) {
        $mensaje = 'No se pudo validar el formulario.';
        $tipoMensaje = 'error';
    } else {
        try {
            if (isset($_POST['añadir_categoria'])) {
                $nombreCategoria = trim($_POST['nombre_categoria'] ?? '');

                if ($nombreCategoria === '' || mb_strlen($nombreCategoria) > 100) {
                    throw new InvalidArgumentException('Ingresa un nombre de categoría de hasta 100 caracteres.');
                }

                agregarCategoria($nombreCategoria);
                $mensaje = 'Categoría agregada correctamente.';
            } else {
                $idCategoria = filter_var(
                    $_POST['id_categoria'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$idCategoria || !desactivarCategoria($idCategoria)) {
                    throw new InvalidArgumentException('No se encontró una categoría activa para eliminar.');
                }

                $mensaje = 'Categoría eliminada correctamente.';
            }

            $categorias = obtenerCategoriasActivas();
            $tipoMensaje = 'categoria_exito';
            $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
            
        } catch (Throwable $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }
    }
}

?>

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

    <div class = "filterSelectorContainer">

        <div class = "buttonFilterSelectorContainer">

            <button type = "button"
                class="buttonFilterSelector active"
                data-filter="todos"
                aria-pressed="true">
                Todos
                <span class="filterCount"><?= $ticketsTodos ?></span>
            </button>

            <button type = "button"
                class="buttonFilterSelector"
                data-filter="Pendiente"
                aria-pressed="false">
                Pendientes
                <span class="filterCount"><?= $cantidadPendientes ?></span>
            </button>

            <button type = "button"
                class="buttonFilterSelector"
                data-filter="En proceso"
                aria-pressed="false">
                En proceso
                <span class="filterCount"><?= $ticketsEnProceso ?></span>
            </button>

            <button type = "button"
                class="buttonFilterSelector"
                data-filter="Resuelto"
                aria-pressed="false">
                Cerrados
                <span class="filterCount"><?= $ticketsResueltos ?></span>
            </button>

        </div>

    </div>

    <div class="dashboardSearchFilter">
        <div class="dashboardSearchTitle">
            <span class="dashboardSearchIcon"><i data-lucide="search"></i></span>
            <div>
                <h2>Buscar tickets</h2>
                <p>Filtra por funcionario o ubicación.</p>
            </div>
        </div>

        <div class="dashboardSearchControls">
            <label class="visuallyHidden" for="criterioBusquedaTicket">Buscar por</label>
            <select id="criterioBusquedaTicket" aria-label="Buscar por">
                <option value="funcionario">Funcionario</option>
                <option value="ubicacion">Ubicación</option>
            </select>
            <div class="dashboardSearchInputWrap">
                <i data-lucide="search" aria-hidden="true"></i>
                <input
                    type="search"
                    id="busquedaTicket"
                    placeholder="Escribe para buscar..."
                    aria-label="Texto a buscar"
                    autocomplete="off"
                >
            </div>
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

                    <tr
                        data-estado="<?= htmlspecialchars($ticket['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-funcionario="<?= htmlspecialchars($ticket['funcionario'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-ubicacion="<?= htmlspecialchars($ticket['lugar'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                        
                        <td data-label="ID">
                            <?= htmlspecialchars((string) $ticket['id']) ?>
                        </td>

                        <td data-label="Funcionario">
                            <?= htmlspecialchars($ticket['funcionario'] ?? '') ?>
                        </td>

                        <td data-label="Ubicación">
                            <?= htmlspecialchars($ticket['lugar']) ?>
                        </td>

                        <td data-label="Descripción">
                            <?= htmlspecialchars($ticket['descripcion'] ?? '') ?>
                        </td>
                        
                        <td class="estadoContainer" data-label="Estado">
                            <span class = "state <?= htmlspecialchars(str_replace(' ', '-', strtolower(trim($ticket['estado'] ?? '')))) ?>">
                                <?= htmlspecialchars($ticket['estado'] ?? '') ?>
                            </span>
                        </td>

                        <td class="iconContainer" data-label="Acciones">
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

                    <tr class = "ticketDateRow" data-estado="<?= htmlspecialchars($ticket['estado'], ENT_QUOTES, 'UTF-8') ?>">
                        <td colspan = "6">

                            <span class = "recordMarker">
                                <span class = "textMarker">Creado el </span>
                                <?= htmlspecialchars(
                                    fechaTicketEnEspanol((string) $ticket['fecha_creacion']),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                                <span class = "textMarker"> por </span>
                                <span><?= htmlspecialchars((string) ($ticket['funcionario'] ?? 'Usuario desconocido'), ENT_QUOTES, 'UTF-8') ?></span>
                            </span>

                        </td>
                    </tr>

                <?php endforeach; ?>

                <tr id="sinResultadosFiltro" hidden>
                    <td colspan="6">No se encontraron resultados con esos parámetros.</td>
                </tr>

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

    <nav class = "ticketPagination" id = "paginacionTickets" aria-label = "Paginación de tickets" hidden>
        <button type = "button" id = "paginaAnterior" aria-label = "Página anterior">Anterior</button>
        <span id = "informacionPaginacion" aria-live = "polite"></span>
        <button type = "button" id = "paginaSiguiente" aria-label = "Página siguiente">Siguiente</button>
    </nav>

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
    
