
<div class="managementPage">
    <div class="sectionHeader misTicketsHeader">
        <h1>Administración</h1>
        <p>Administra las categorías y dependencias disponibles para los tickets.</p>
    </div>

    <?php if ($tipoMensaje === 'error'): ?>
        <p class="ticket-form-error" role="alert">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php elseif ($tipoMensaje !== ''): ?>
        <p class="managementMessage" role="status">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <section class="categoryManagement">
        <div class="managementSectionHeader">
            <h2>Administrar categorías</h2>
            <p>Agrega categorías o quita las que ya no se usan.</p>
        </div>

        <form class="categoryCreateForm" method="POST" action="/public/index.php?seccion=configuracion">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="form-group">
                <label for="nombre_categoria">Nueva categoría</label>
                <input
                    type="text"
                    id="nombre_categoria"
                    name="nombre_categoria"
                    maxlength="100"
                    required
                >
            </div>

            <button type="submit" name="añadir_categoria">Agregar categoría</button>
        </form>

        <h3 class="managementListTitle">Categorías existentes</h3>

        <?php if (empty($categorias)): ?>
            <p class="managementEmptyState">No hay categorías activas.</p>
        <?php else: ?>
            <ul class="categoryList">
                <?php foreach ($categorias as $categoria): ?>
                    <li class="categoryListItem">
                        <span><?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                        <form
                            method="POST"
                            action="/public/index.php?seccion=configuracion"
                            data-confirm-delete
                            data-confirm-title="Eliminar categoría"
                            data-confirm-message="¿Eliminar esta categoría? Dejará de estar disponible para nuevos tickets."
                            data-confirm-button="Eliminar categoría"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>"
                            >
                            <input
                                type="hidden"
                                name="id_categoria"
                                value="<?= (int) $categoria['id'] ?>"
                            >
                            <button class="categoryDeleteButton" type="submit" name="eliminar_categoria">Eliminar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="categoryManagement">
        <div class="managementSectionHeader">
            <h2>Administrar dependencias</h2>
            <p>Agrega dependencias o quita las que ya no se usan.</p>
        </div>

        <form class="categoryCreateForm" method="POST" action="/public/index.php?seccion=configuracion">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>"
            >
            <div class="form-group">
                <label for="nombre_dependencia">Nueva dependencia</label>
                <input
                    type="text"
                    id="nombre_dependencia"
                    name="nombre_dependencia"
                    maxlength="100"
                    required
                >
            </div>
            <button type="submit" name="añadir_dependencia">Agregar dependencia</button>
        </form>

        <h3 class="managementListTitle">Dependencias existentes</h3>
        
        <?php if (empty($lugares)): ?>
            <p class="managementEmptyState">No hay dependencias activas.</p>
        <?php else: ?>
            
            <ul class="categoryList">
                <?php foreach ($lugares as $dependencia): ?>
                    <li class="categoryListItem">
                        <span><?= htmlspecialchars($dependencia['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                        <form
                            method="POST"
                            action="/public/index.php?seccion=configuracion"
                            data-confirm-delete
                            data-confirm-title="Eliminar dependencia"
                            data-confirm-message="¿Eliminar esta dependencia? Dejará de estar disponible para nuevos tickets."
                            data-confirm-button="Eliminar dependencia"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfTicket, ENT_QUOTES, 'UTF-8') ?>"
                            >
                            <input
                                type="hidden"
                                name="id_dependencia"
                                value="<?= (int) $dependencia['id'] ?>"
                            >
                            <button class="categoryDeleteButton" type="submit" name="eliminar_dependencia">Eliminar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

