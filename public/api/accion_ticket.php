<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/controllers/TicketActionController.php';

$tiempoMaximoSesion = 1800;
$ultimaActividad = (int) ($_SESSION['ultima_actividad'] ?? time());

if (!isset($_SESSION['usuario']) || (time() - $ultimaActividad) > $tiempoMaximoSesion) {
    $_SESSION = [];
    session_destroy();

    responderAccionTicket(401, [
        'success' => false,
        'message' => 'Tu sesión venció. Inicia sesión nuevamente.'
    ]);
}

$_SESSION['ultima_actividad'] = time();
procesarAccionTicketJson();
