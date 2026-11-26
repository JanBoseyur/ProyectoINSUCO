<?php

require_once __DIR__ . '/TicketController.php';

function responderAccionTicket(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function procesarAccionTicketJson(): never
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderAccionTicket(405, ['success' => false, 'message' => 'Método no permitido.']);
    }

    $usuario = $_SESSION['usuario'] ?? null;
    if (!is_array($usuario)) {
        responderAccionTicket(403, ['success' => false, 'message' => 'No tienes permiso para realizar esta acción.']);
    }

    $datos = json_decode(file_get_contents('php://input'), true);
    if (!is_array($datos)) {
        responderAccionTicket(400, ['success' => false, 'message' => 'Datos inválidos.']);
    }

    $accion = $datos['accion'] ?? '';
    $idTicket = filter_var($datos['id_ticket'] ?? null, FILTER_VALIDATE_INT);
    if (!$idTicket || $idTicket < 1) {
        responderAccionTicket(400, ['success' => false, 'message' => 'ID de ticket inválido.']);
    }

    if ($accion === 'eliminar_ticket') {
        if (($usuario['rol'] ?? '') !== 'admin') {
            responderAccionTicket(403, ['success' => false, 'message' => 'No tienes permiso para eliminar tickets.']);
        }

        if (!eliminarTicket($idTicket)) {
            responderAccionTicket(400, ['success' => false, 'message' => 'No se pudo eliminar el ticket.']);
        }

        responderAccionTicket(200, ['success' => true, 'message' => 'Ticket eliminado correctamente.']);
    }

    $estado = trim((string) ($datos['estado'] ?? ''));
    $descripcion = trim((string) ($datos['descripcion'] ?? ''));
    $encargado = trim((string) ($datos['encargado'] ?? ''));
    $estadosPermitidos = ['Pendiente', 'En proceso', 'Resuelto'];

    if (
        $accion !== 'cambiar_estado' ||
        !in_array($estado, $estadosPermitidos, true) ||
        mb_strlen($descripcion) > 1000 ||
        mb_strlen($encargado) > 150
    ) {
        responderAccionTicket(400, ['success' => false, 'message' => 'Los datos enviados no son válidos.']);
    }

    try {
        $actualizado = cambiarEstado(
            $idTicket,
            $estado,
            (int) ($usuario['id'] ?? 0),
            $descripcion,
            $encargado !== '' ? $encargado : null
        );

        if (!$actualizado) {
            responderAccionTicket(400, ['success' => false, 'message' => 'No fue posible actualizar el ticket.']);
        }

        responderAccionTicket(200, [
            'success' => true,
            'message' => 'El estado del ticket se actualizó correctamente.'
        ]);
    } catch (Throwable $e) {
        error_log('Error al cambiar estado del ticket: ' . $e->getMessage());
        responderAccionTicket(500, ['success' => false, 'message' => 'Ocurrió un error al procesar la solicitud.']);
    }
}
