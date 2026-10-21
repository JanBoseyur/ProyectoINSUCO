
<?php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    !in_array($_SESSION['usuario']['rol'], ['admin', 'tecnico'], true)
) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];

###############################################################################
# Tickets
###############################################################################

require_once __DIR__ . '/../../controllers/TicketController.php';

$tickets = obtenerTickets();
$ticketsHoy = obtenerTicketsDeHoy();
$ticketsAbiertos = obtenerTicketsAbiertos();
$ticketsEnProceso = obtenerTicketsEnProceso();
$ticketsResueltos = obtenerTicketsResueltos();

$estado = strtolower(trim($ticket['estado'] ?? ''));
?>

<!DOCTYPE html>

<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">
aa
    <title>Dashboard</title>

    <link rel = "stylesheet" href = "/public/css/dashboard.css">
    <link rel = "stylesheet" href = "/public/components/metricCard.css">
    <link rel = "stylesheet" href = "/public/css/navbar.css">
</head>

<body>

    <div class = "mainContainer">

        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <div class = "contentContainer">

            <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>

            <text class = "subtitle">Aquí puedes supervisar las solicitudes de soporte técnico y el estado de atención de la institución</h2>

            <div class = "graphsContainer">

                <?php
                    $titulo = 'Tickets de Hoy';
                    $subtitulo = 'Tickets registrados el día de hoy';
                    $icono = 'ticket';
                    $valor = $ticketsHoy;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

                <?php
                    $titulo = 'Tickets Abiertos';
                    $subtitulo = 'Pendientes de resolución';
                    $icono = 'clock';
                    $valor = $ticketsAbiertos;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

                <?php
                    $titulo = 'Tickets en Proceso';
                    $subtitulo = 'Solicitudes que están siendo atendidas';
                    $icono = 'wrench';
                    $valor = $ticketsEnProceso;

                    include __DIR__ . '/../components/metricCard.php';
                ?>
                
                <?php
                    $titulo = 'Tickets Resueltos';
                    $subtitulo = 'Solicitudes correctamente terminadas';
                    $icono = 'circle-check-big';
                    $valor = $ticketsResueltos;

                    include __DIR__ . '/../components/metricCard.php';
                ?>

            </div>

            <div class = "ticketContainer">

                <table class = "ticketTable">
                    
                    <thead class = "col">
                    
                        <tr class = "colContainer">
                            <th>
                                <div class = "headerLabel">             
                                    <span>ID</span>
                                </div>
                            </div>    </th>

                            <th>
                                <div class = "headerLabel">
                                    <span>Funcionario</span>
                                </div>
                            </th>
                            
                            <th>
                                <div class = "headerLabel">
                                    <span>Ubicación</span>
                                </div>
                            </th>
                        
                            <th>
                                <div class = "headerLabel">
                                    <span>Descripción</span>
                                </div>
                            </th>

                            <th>
                                <div class = "headerLabel">
                                    <span>Estado</span>
                                </div>
                            </th>
                        </tr>

                    </thead>

                    <tbody class = "contenidoTabla">

                        <?php foreach ($tickets as $ticket): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) $ticket['id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['funcionario'] ?? '') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['lugar']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($ticket['descripcion'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="state <?= htmlspecialchars(str_replace(' ', '-', strtolower(trim($ticket['estado'] ?? '')))) ?>">
                                        <?= htmlspecialchars($ticket['estado'] ?? '') ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan = "4">No hay tickets registrados.</td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src = "/js/app.js"></script>
</body>

</html>
