
<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

$usuario = $_SESSION['usuario'] ?? null;
$rol = is_array($usuario) ? ($usuario['rol'] ?? null) : null;

if (
    !is_array($usuario) ||
    !in_array($rol, ['admin', 'tecnico'], true)
) {
    http_response_code(403);

    echo json_encode([
        'error' => 'No tienes permiso para consultar el historial.'
    ]);

    exit;
}

$idTicket = filter_input(
    INPUT_GET,
    'id_ticket',
    FILTER_VALIDATE_INT
);

if ($idTicket === false || $idTicket === null || $idTicket < 1) {
    http_response_code(400);

    echo json_encode([
        'error' => 'El ID del ticket no es válido.'
    ]);

    exit;
}

require_once __DIR__ . '/../../app/controllers/TicketController.php';

try {
    $historial = obtenerHistorialTicket($idTicket);

    echo json_encode(
        $historial,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'No se pudo obtener el historial.'
    ]);
}
