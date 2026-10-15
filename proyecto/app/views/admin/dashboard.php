
<?php

session_start();

if (
    !isset($_SESSION['usuario']) ||
    $_SESSION['usuario']['rol'] !== 'admin'
) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta página.');
}

$nombre = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];

# Tickets
require_once __DIR__ . '/../../controllers/TicketController.php';
$tickets = obtenerTickets();

# Estado
$claseEstado = $clasesEstado[$estado] ?? 'state-default';
?>

<!DOCTYPE html>

<html lang = "es">

<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <link rel = "stylesheet" href = "/public/css/dashboard.css">
</head>

<body>
    <h2>Bienvenido, <?= htmlspecialchars($nombre) ?>!</h2>

    <a href = "/app/logout.php">Cerrar sesión</a>

    <div class = "ticketContainer">

        <table class = "ticketTable">
            
            <thead class = "col">
               
                <tr>
                    <th>
                        <div class = "headerLabel">             
                            <span>ID</span>
                        </div>
                    </th>

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

            <tbody>

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

                        <td class = "state">
                            <?= htmlspecialchars($ticket['estado'] ?? '') ?>
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
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script src = "/js/app.js"></script>
</body>

</html>
