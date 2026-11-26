<?php

require_once __DIR__ . '/../app/controllers/ApplicationController.php';

$solicitud = despacharSolicitudAplicacion();
extract($solicitud['datos'], EXTR_SKIP);

require $solicitud['vista'];
