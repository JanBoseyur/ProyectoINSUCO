
    <div class = "sectionHeader misTicketsHeader misTicketsPageHeader">
        <h1>Mis tickets</h1>
        <p>Consulta y realiza seguimiento de tus solicitudes de soporte técnico.</p>
    </div>

    <div class = "filterSelectorContainer" aria-label = "Filtrar tickets por estado">
        
        <div class = "buttonFilterSelectorContainer">
            <button type = "button" class = "buttonFilterSelector active" data-filter="todos" aria-pressed="true">
                Todos <span class = "filterCount"><?= count($tickets) ?></span>
            </button>
            
            <button type = "button" class = "buttonFilterSelector" data-filter="Pendiente" aria-pressed="false">
                Pendientes <span class = "filterCount"><?= $ticketsPendientes ?></span>
            </button>
            
            <button type = "button" class = "buttonFilterSelector" data-filter="En proceso" aria-pressed="false">
                En proceso <span class = "filterCount"><?= $ticketsEnProceso ?></span>
            </button>

            <button type = "button" class = "buttonFilterSelector" data-filter="Resuelto" aria-pressed="false">
                Resueltos <span class = "filterCount"><?= $ticketsResueltos ?></span>
            </button>
        </div>

    </div>

    <div class = "ticketContainer misTicketsTableContainer">
        
        <table class = "ticketTable">
            
            <thead class = "col">
            
                <tr class = "colContainer">
                    <th>
                        <div class = "headerLabel misTicketsColumnTitle">
                            <span>Categoría</span>
                        </div>
                    </th>

                    <th>
                        <div class = "headerLabel misTicketsColumnTitle">
                            <span>Ubicación</span>
                        </div>
                    </th>
                
                    <th>
                        <div class = "headerLabel misTicketsColumnTitle">
                            <span>Descripción</span>
                        </div>
                    </th>

                    <th>
                        <div class = "headerLabel misTicketsColumnTitle">
                            <span>Estado</span>
                        </div>
                    </th>

                    <th>
                        <div class = "headerLabel misTicketsColumnTitle">
                            <span>Acciones</span>
                        </div>
                    </th>
                </tr>

            </thead>

            <tbody class = "contenidoTabla" id = "tablaTickets">

               <?php foreach ($tickets as $ticket): ?>

                    <?php $descripcionTicket = (string) ($ticket['descripcion'] ?? ''); ?>

                    <tr data-estado = "<?= htmlspecialchars($ticket['estado']) ?>">

                        <td data-label="Categoría">
                            <?= htmlspecialchars($ticket['categoria']) ?>
                        </td>

                        <td data-label="Ubicación">
                            <?= htmlspecialchars($ticket['lugar'] ?? '') ?>
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
                        
                        <td class = "estadoContainer" data-label="Estado">
                            <span class = "state <?= htmlspecialchars(str_replace(' ', '-', strtolower(trim($ticket['estado'] ?? '')))) ?>">
                                <?= htmlspecialchars($ticket['estado'] ?? '') ?>
                            </span>
                        </td>

                        <td class = "buttonsContainerFunctions" data-label="Acciones">
                            <button
                                type = "button"
                                class = "btnHistorial btnEditarTicket"
                                data-editar-ticket
                                data-id = "<?= (int) $ticket['id'] ?>"
                                data-id-lugar = "<?= (int) ($ticket['id_lugar'] ?? 0) ?>"
                                data-categoria = "<?= htmlspecialchars($ticket['categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-descripcion = "<?= htmlspecialchars($ticket['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                title = "Editar ticket"
                                aria-label = "Editar ticket <?= (int) $ticket['id'] ?>"
                            >
                                <i data-lucide = "pencil" class="iconDefault"></i>
                                <i data-lucide = "pencil-line" class="iconHover"></i>
                            </button>
                        </td>
                        
                    </tr>

                    <tr class = "ticketDateRow" data-estado="<?= htmlspecialchars($ticket['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <td colspan = "5">
                            <span class = "recordMarker">
                                <span class = "textMarker">Creado el </span>
                                <?= htmlspecialchars(fechaTicketEnEspanol((string) $ticket['fecha_creacion']), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php
                    require __DIR__ . '/../components/modalState.php';
                ?>

                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan = "5">No hay tickets registrados.</td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <nav class = "ticketPagination" id = "paginacionTickets" aria-label = "Paginación de tickets" hidden>
        <button type = "button" id = "paginaAnterior" aria-label = "Página anterior">Anterior</button>
        <span id = "informacionPaginacion" aria-live = "polite"></span>
        <button type = "button" id = "paginaSiguiente" aria-label="Página siguiente">Siguiente</button>
    </nav>

    <div class = "modalOverlay" id = "modalEditarTicket" aria-hidden = "true">
        
        <section class = "modalContainer ticketEditModal" role = "dialog" aria-modal = "true" aria-labelledby = "tituloEditarTicket">
           
            <header class = "modalHeader">

                <div class = "modalHistorialIcon">
                    <i data-lucide = "square-pen"></i>
                </div>

                <h2 id = "tituloEditarTicket">Editar ticket</h2>
                <button type = "button" class = "modalClose" data-modal-close aria-label="Cerrar modal">&times;</button>
            </header>

            <form id = "formEditarTicket" class = "ticketEditForm">
                <input type = "hidden" name = "id_ticket" id = "editarIdTicket">
                <input type = "hidden" name = "csrf_token" value="<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>">

                <label for = "editarCategoria">Categoría</label>
                <select name = "categoria" id = "editarCategoria" required>
                    
                    <?php foreach ($categorias as $categoria): ?>
                        <option value = "<?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>

                </select>

                <label for = "editarLugar">Ubicación</label>
                <select name = "id_lugar" id = "editarLugar" required>

                    <?php foreach ($lugares as $lugar): ?>
                        <option value = "<?= (int) $lugar['id'] ?>"><?= htmlspecialchars($lugar['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>

                </select>

                <label for = "editarDescripcion">Descripción</label>
                <textarea name = "descripcion" id = "editarDescripcion" rows="5" maxlength="1000" required></textarea>

                <p id = "mensajeEditarTicket" class = "ticketEditMessage" role="alert" aria-live="polite"></p>
                <div class = "ticketEditActions">
                    <button type = "button" class = "ticketEditCancel" data-modal-close>Cancelar</button>
                    <button type = "submit" class = "ticketEditSave">Guardar cambios</button>
                </div>
            </form>

        </section>

    </div>

