
<?php

require_once __DIR__ . '/../../controllers/TicketController.php';

$usuario = $_SESSION['usuario'] ?? null;
if (!is_array($usuario) || ($usuario['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

if (empty($_SESSION['csrf_ticket'])) {
    $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
}

$mensaje = '';
$tipoMensaje = '';
$categorias = obtenerCategoriasActivas();
$lugares = obtenerLugaresActivos();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (
        isset($_POST['añadir_categoria']) ||
        isset($_POST['eliminar_categoria']) ||
        isset($_POST['añadir_dependencia']) ||
        isset($_POST['eliminar_dependencia'])
    )
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
                $tipoMensaje = 'management_success';
            } elseif (isset($_POST['eliminar_categoria'])) {
                $idCategoria = filter_var(
                    $_POST['id_categoria'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$idCategoria || !desactivarCategoria($idCategoria)) {
                    throw new InvalidArgumentException('No se encontró una categoría activa para eliminar.');
                }

                $mensaje = 'Categoría eliminada correctamente.';
                $tipoMensaje = 'management_success';
            } elseif (isset($_POST['añadir_dependencia'])) {
                $nombreDependencia = trim($_POST['nombre_dependencia'] ?? '');

                if ($nombreDependencia === '' || mb_strlen($nombreDependencia) > 100) {
                    throw new InvalidArgumentException('Ingresa un nombre de dependencia de hasta 100 caracteres.');
                }

                agregarDependenciaActiva($nombreDependencia);
                $mensaje = 'Dependencia agregada correctamente.';
                $tipoMensaje = 'management_success';
            } else {
                $idDependencia = filter_var(
                    $_POST['id_dependencia'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$idDependencia || !desactivarDependencia($idDependencia)) {
                    throw new InvalidArgumentException('No se encontró una dependencia activa para eliminar.');
                }

                $mensaje = 'Dependencia eliminada correctamente.';
                $tipoMensaje = 'management_success';
            }

            $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }
    }

    $categorias = obtenerCategoriasActivas();
    $lugares = obtenerLugaresActivos();
}

?>

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

        <form class="categoryCreateForm" method="POST" action="">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_ticket'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
                            action=""
                            data-confirm-delete
                            data-confirm-title="Eliminar categoría"
                            data-confirm-message="¿Eliminar esta categoría? Dejará de estar disponible para nuevos tickets."
                            data-confirm-button="Eliminar categoría"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_ticket'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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

        <form class="categoryCreateForm" method="POST" action="">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_ticket'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
                            action=""
                            data-confirm-delete
                            data-confirm-title="Eliminar dependencia"
                            data-confirm-message="¿Eliminar esta dependencia? Dejará de estar disponible para nuevos tickets."
                            data-confirm-button="Eliminar dependencia"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($_SESSION['csrf_ticket'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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

<script src = "https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
<script src="/public/js/modalConfirmacion.js" defer></script>
