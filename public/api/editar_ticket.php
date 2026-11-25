<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/controllers/TicketController.php';

header('Content-Type: application/json; charset=utf-8');

$responder = static function (int $codigo, array $respuesta): void {
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
};

$usuario = $_SESSION['usuario'] ?? null;
if (!is_array($usuario) || ($usuario['rol'] ?? '') !== 'funcionario') {
    $responder(403, ['success' => false, 'message' => 'No tienes permiso para editar tickets.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $responder(405, ['success' => false, 'message' => 'Método no permitido.']);
}

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    $responder(400, ['success' => false, 'message' => 'Datos no válidos.']);
}

$token = $datos['csrf_token'] ?? '';
if (
    !is_string($token) ||
    empty($_SESSION['csrf_ticket']) ||
    !hash_equals($_SESSION['csrf_ticket'], $token)
) {
    $responder(403, ['success' => false, 'message' => 'No se pudo validar la solicitud.']);
}

$idTicket = filter_var($datos['id_ticket'] ?? null, FILTER_VALIDATE_INT);
$idLugar = filter_var($datos['id_lugar'] ?? null, FILTER_VALIDATE_INT);
$categoria = trim((string) ($datos['categoria'] ?? ''));
$descripcion = trim((string) ($datos['descripcion'] ?? ''));

if (!$idTicket || !$idLugar) {
    $responder(400, ['success' => false, 'message' => 'El ticket o la ubicación no son válidos.']);
}

try {
    $actualizado = editarTicketFuncionario(
        $idTicket,
        (int) $usuario['id'],
        $idLugar,
        $categoria,
        $descripcion
    );

    if (!$actualizado) {
        $responder(400, ['success' => false, 'message' => 'No se pudo guardar. Revisa los datos e inténtalo de nuevo.']);
    }

    $responder(200, ['success' => true, 'message' => 'Ticket actualizado correctamente.']);
} catch (Throwable $e) {
    error_log('Error al editar ticket: ' . $e->getMessage());
    $responder(500, ['success' => false, 'message' => 'Ocurrió un error al actualizar el ticket.']);
}
