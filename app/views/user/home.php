
    <div class = "titleContainer homeWelcome">
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

        <form class = "formularioContainer" method = "POST" action ="/public/index.php?seccion=home">

            <div class="ticketFormHeading">
                <span class="ticketFormIcon" aria-hidden="true">
                    <i data-lucide="ticket-plus"></i>
                </span>
                <div class="sectionHeader misTicketsHeader">
                    <h1>Crear nuevo ticket</h1>
                    <p>Cuéntanos qué sucede y te ayudaremos a resolverlo.</p>
                </div>
            </div>

            <input
                type = "hidden"
                name = "csrf_token"
                value = "<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>"
            >
            
            <div class = "selects-container">
                <div class = "form-group">
                    <label for = "lugar">Lugar:</label>

                    <select name = "lugar" id = "lugar" class = "filterSelect" required>
                        <option value="">Seleccionar lugar</option>

                        <?php foreach ($lugares as $opcionLugar): ?>
                            <option
                                value="<?= (int) $opcionLugar['id'] ?>"
                                <?= $lugarSeleccionado === (string) $opcionLugar['id'] ? 'selected' : '' ?>
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
                                    <?= $categoriaSeleccionada === (string) $opcionCategoria['nombre'] ? 'selected' : '' ?>
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
                ><?= htmlspecialchars($descripcionIngresada, ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="ticketFormHint">
                    <i data-lucide="info" aria-hidden="true"></i>
                    <span>Incluye detalles que ayuden a identificar el problema. Máximo 1000 caracteres.</span>
                </p>
            </div>

            <div class="ticketFormActions">
                <button type="submit" name="crear_ticket">
                    <span>Enviar ticket</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </button>
            </div>

        </form>

    </div>

    <?php require __DIR__ . '/../components/modalNotificacion.php'; ?>

