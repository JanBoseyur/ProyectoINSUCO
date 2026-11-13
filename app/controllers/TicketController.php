
<?php

require_once __DIR__ . '/../models/Ticket.php';

# Funciones Administrativas
function obtenerTickets(): array
{
    return listarTickets();
}

function obtenerTicketsAbiertos(): int
{
    return listarTicketsAbiertos();
}

function obtenerTicketsEnProceso(): int
{
    return listarTicketsEnProceso();
}

function obtenerTicketsResueltos(): int
{
    return listarTicketsResueltos();
}

function obtenerTicketsFuncionario(int $idUsuario): array
{
    return listarTicketsPorUsuario($idUsuario);
}

function cambiarEstado(
    int $idTicket,
    string $estadoNuevo,
    int $idAutor,
    string $descripcionCambio = '',
    ?string $nuevoEncargado = null

): bool {
    
    return actualizarEstadoTicket(
        $idTicket,
        $estadoNuevo,
        $idAutor,
        $descripcionCambio,
        $nuevoEncargado
    );
}

function registrarTicket(
    int $idUsuario,
    string $lugar,
    string $descripcion
): int {
    return insertarTicket($idUsuario, $lugar, $descripcion);
}

# Funciones de Usuario
function procesarCreacionTicket(): void
{
    $usuario = $_SESSION['usuario'] ?? null;

    if (
        !is_array($usuario) ||
        ($usuario['rol'] ?? null) !== 'funcionario'
    ) {
        http_response_code(403);
        exit('No tienes permiso para realizar esta acción.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        empty($_SESSION['csrf_ticket']) ||
        !hash_equals($_SESSION['csrf_ticket'], $token)
    ) {
        $_SESSION['notificacion'] = [
            'tipo' => 'error',
            'titulo' => 'Error de validación',
            'mensaje' => 'No se pudo validar el formulario.'
        ];

        return;
    }

    try {
        $lugar = trim($_POST['lugar'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $idTicket = registrarTicket(
            (int) $usuario['id'],
            $lugar,
            $descripcion
        );

        $_SESSION['notificacion'] = [
            'tipo' => 'exito',
            'titulo' => '¡Ticket creado con éxito!',
            'mensaje' => 'El ticket ha sido registrado correctamente.'
        ];

        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));

    } catch (Throwable $e) {
        error_log($e->getMessage());

        $_SESSION['notificacion'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'mensaje' => 'No fue posible registrar el ticket.'
        ];
    }
}
