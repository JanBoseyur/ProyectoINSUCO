
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

    <div class = "dashboardSearchFilter">
        
        <div class = "dashboardSearchTitle">
            <span class = "dashboardSearchIcon"><i data-lucide="search"></i></span>
            <div>
                <h2>Buscar tickets</h2>
                <p>Filtra por funcionario o ubicación.</p>
            </div>
        </div>

        <div class = "dashboardSearchControls">
            <label class = "visuallyHidden" for = "criterioBusquedaTicket">Buscar por</label>
            
            <select class = "selectBusqueda" id = "criterioBusquedaTicket" aria-label="Buscar por">
                <option value = "funcionario">Funcionario</option>
                <option value = "ubicacion">Ubicación</option>
            </select>

            <div class = "dashboardSearchInputWrap">
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

                    <?php $descripcionTicket = (string) ($ticket['descripcion'] ?? ''); ?>

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
                            <div class="ticketDescription<?= mb_strlen($descripcionTicket, 'UTF-8') > 180 ? ' isExpandable' : '' ?>">
                                <span class="ticketDescriptionText"><?= htmlspecialchars($descripcionTicket, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (mb_strlen($descripcionTicket, 'UTF-8') > 180): ?>
                                    <button
                                        type="button"
                                        class="ticketDescriptionToggle"
                                        data-description-toggle
                                        data-ticket-id="<?= (int) $ticket['id'] ?>"
                                        aria-expanded="false"
                                        aria-label="Expandir descripción del ticket <?= (int) $ticket['id'] ?>"
                                    >Ver más</button>
                                <?php endif; ?>
                            </div>
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
                                title = "Eliminar ticket"
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
                            </span>

                        </td>
                    </tr>

                <?php endforeach; ?>

                <tr id = "sinResultadosFiltro" hidden>
                    <td colspan = "6">No se encontraron resultados con esos parámetros.</td>
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

