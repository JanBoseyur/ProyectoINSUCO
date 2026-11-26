<?php

require_once __DIR__ . '/TicketController.php';

function asegurarCsrfTicket(): string
{
    if (empty($_SESSION['csrf_ticket'])) {
        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_ticket'];
}

function validarCsrfTicket(mixed $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_ticket'])
        && hash_equals($_SESSION['csrf_ticket'], $token);
}

function procesarPostDashboardAdmin(): array
{
    $mensaje = '';
    $tipoMensaje = '';

    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        (!isset($_POST['añadir_categoria']) && !isset($_POST['eliminar_categoria']))
    ) {
        return [$mensaje, $tipoMensaje];
    }

    if (!validarCsrfTicket($_POST['csrf_token'] ?? '')) {
        return ['No se pudo validar el formulario.', 'error'];
    }

    try {
        if (isset($_POST['añadir_categoria'])) {
            $nombre = trim((string) ($_POST['nombre_categoria'] ?? ''));
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('Ingresa un nombre de categoría de hasta 100 caracteres.');
            }
            agregarCategoria($nombre);
            $mensaje = 'Categoría agregada correctamente.';
        } else {
            $id = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !desactivarCategoria($id)) {
                throw new InvalidArgumentException('No se encontró una categoría activa para eliminar.');
            }
            $mensaje = 'Categoría eliminada correctamente.';
        }

        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
        $tipoMensaje = 'categoria_exito';
    } catch (Throwable $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipoMensaje = 'error';
    }

    return [$mensaje, $tipoMensaje];
}

function obtenerDatosDashboardAdmin(array $usuario): array
{
    asegurarCsrfTicket();
    [$mensaje, $tipoMensaje] = procesarPostDashboardAdmin();
    $tickets = obtenerTickets();

    return [
        'usuario' => $usuario,
        'nombre' => $usuario['nombre'] ?? '',
        'tickets' => $tickets,
        'categorias' => obtenerCategoriasActivas(),
        'mensaje' => $mensaje,
        'tipoMensaje' => $tipoMensaje,
        'ticketsHoy' => obtenerTicketsDeHoy(),
        'ticketsTodos' => count($tickets),
        'ticketsAbiertos' => obtenerTicketsAbiertos(),
        'ticketsEnProceso' => obtenerTicketsEnProceso(),
        'ticketsResueltos' => obtenerTicketsResueltos(),
        'cantidadPendientes' => count(array_filter(
            $tickets,
            static fn(array $ticket): bool => ($ticket['estado'] ?? '') === 'Pendiente'
        )),
        'csrfTicket' => $_SESSION['csrf_ticket']
    ];
}

function procesarPostHome(array $usuario): array
{
    $mensaje = '';
    $tipoMensaje = '';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [$mensaje, $tipoMensaje];
    }

    if (!validarCsrfTicket($_POST['csrf_token'] ?? '')) {
        return ['No se pudo validar el formulario.', 'error'];
    }

    try {
        if (isset($_POST['añadir_categoria'])) {
            $nombre = trim((string) ($_POST['nombre_categoria'] ?? ''));
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('Ingresa un nombre de categoría de hasta 100 caracteres.');
            }
            agregarCategoria($nombre);
            $mensaje = 'Categoría agregada correctamente.';
            $tipoMensaje = 'categoria_exito';
        } elseif (isset($_POST['crear_ticket'])) {
            registrarTicket(
                (int) $usuario['id'],
                trim((string) ($_POST['lugar'] ?? '')),
                trim((string) ($_POST['descripcion'] ?? '')),
                trim((string) ($_POST['categoria'] ?? ''))
            );
            $mensaje = '¡Ticket creado correctamente!';
            $tipoMensaje = 'exito';
        } else {
            return [$mensaje, $tipoMensaje];
        }

        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipoMensaje = 'error';
    }

    return [$mensaje, $tipoMensaje];
}

function obtenerDatosHome(array $usuario): array
{
    asegurarCsrfTicket();
    [$mensaje, $tipoMensaje] = procesarPostHome($usuario);

    return [
        'nombre' => $usuario['nombre'] ?? '',
        'lugares' => obtenerLugaresActivos(),
        'categorias' => obtenerCategoriasActivas(),
        'mensaje' => $mensaje,
        'tipoMensaje' => $tipoMensaje,
        'lugarSeleccionado' => (string) ($_POST['lugar'] ?? ''),
        'categoriaSeleccionada' => (string) ($_POST['categoria'] ?? ''),
        'descripcionIngresada' => (string) ($_POST['descripcion'] ?? ''),
        'csrfTicket' => $_SESSION['csrf_ticket']
    ];
}

function procesarPostAdministracion(): array
{
    $mensaje = '';
    $tipoMensaje = '';
    $acciones = [
        'añadir_categoria', 'eliminar_categoria',
        'añadir_dependencia', 'eliminar_dependencia'
    ];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [$mensaje, $tipoMensaje];
    }

    $accion = null;
    foreach ($acciones as $nombreAccion) {
        if (isset($_POST[$nombreAccion])) {
            $accion = $nombreAccion;
            break;
        }
    }
    if ($accion === null) {
        return [$mensaje, $tipoMensaje];
    }

    if (!validarCsrfTicket($_POST['csrf_token'] ?? '')) {
        return ['No se pudo validar el formulario.', 'error'];
    }

    try {
        if ($accion === 'añadir_categoria') {
            $nombre = trim((string) ($_POST['nombre_categoria'] ?? ''));
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('Ingresa un nombre de categoría de hasta 100 caracteres.');
            }
            agregarCategoria($nombre);
            $mensaje = 'Categoría agregada correctamente.';
        } elseif ($accion === 'eliminar_categoria') {
            $id = filter_var($_POST['id_categoria'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !desactivarCategoria($id)) {
                throw new InvalidArgumentException('No se encontró una categoría activa para eliminar.');
            }
            $mensaje = 'Categoría eliminada correctamente.';
        } elseif ($accion === 'añadir_dependencia') {
            $nombre = trim((string) ($_POST['nombre_dependencia'] ?? ''));
            if ($nombre === '' || mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('Ingresa un nombre de dependencia de hasta 100 caracteres.');
            }
            agregarDependenciaActiva($nombre);
            $mensaje = 'Dependencia agregada correctamente.';
        } else {
            $id = filter_var($_POST['id_dependencia'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !desactivarDependencia($id)) {
                throw new InvalidArgumentException('No se encontró una dependencia activa para eliminar.');
            }
            $mensaje = 'Dependencia eliminada correctamente.';
        }

        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));
        $tipoMensaje = 'management_success';
    } catch (Throwable $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipoMensaje = 'error';
    }

    return [$mensaje, $tipoMensaje];
}

function obtenerDatosAdministracion(): array
{
    asegurarCsrfTicket();
    [$mensaje, $tipoMensaje] = procesarPostAdministracion();

    return [
        'mensaje' => $mensaje,
        'tipoMensaje' => $tipoMensaje,
        'categorias' => obtenerCategoriasActivas(),
        'lugares' => obtenerLugaresActivos(),
        'csrfTicket' => $_SESSION['csrf_ticket']
    ];
}

function obtenerDatosMisTickets(array $usuario): array
{
    asegurarCsrfTicket();
    $tickets = obtenerTicketsFuncionario((int) $usuario['id']);
    $conteo = ['Pendiente' => 0, 'En proceso' => 0, 'Resuelto' => 0];

    foreach ($tickets as $ticket) {
        $estado = $ticket['estado'] ?? '';
        if (isset($conteo[$estado])) {
            $conteo[$estado]++;
        }
    }

    return [
        'tickets' => $tickets,
        'ticketsPendientes' => $conteo['Pendiente'],
        'ticketsEnProceso' => $conteo['En proceso'],
        'ticketsResueltos' => $conteo['Resuelto'],
        'categorias' => obtenerCategoriasActivas(),
        'lugares' => obtenerLugaresActivos(),
        'csrfTicket' => $_SESSION['csrf_ticket'],
        'nombre' => $usuario['nombre'] ?? ''
    ];
}

function obtenerDatosSeccion(string $seccion, array $usuario): array
{
    return match ($seccion) {
        'dashboard' => obtenerDatosDashboardAdmin($usuario),
        'configuracion' => obtenerDatosAdministracion(),
        'home' => obtenerDatosHome($usuario),
        'misTickets' => obtenerDatosMisTickets($usuario),
        default => []
    };
}
