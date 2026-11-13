
<?php

require_once __DIR__ . '/app/helpers/logger.php';

// Conexión a la base de datos
require_once __DIR__ . '/config/database.php';

// Controladores
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/TicketController.php';

session_start();

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/errores.log');

error_log('[PRUEBA] El registro de errores funciona correctamente.');

// Determinar página solicitada
$pagina = $_GET['pagina'] ?? 'login';

// Obtener usuario de la sesión
$usuario = $_SESSION['usuario'] ?? null;

// Rutas
switch ($pagina) {

    case 'login':
        require_once __DIR__ . '/app/views/auth/login.php';
        break;

    case 'dashboard':

        if (!is_array($usuario)) {
            header('Location: index.php?pagina=login');
            exit;
        }

        if (($usuario['rol'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('No tienes permiso para acceder a esta página.');
        }

        require_once __DIR__ . '/app/views/admin/dashboard.php';
        break;

    case 'home':

        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?pagina=login');
            exit;
        }

        if (($_SESSION['usuario']['rol'] ?? '') !== 'funcionario') {
            http_response_code(403);
            exit('Acceso no autorizado.');
        }

        require_once __DIR__ . '/app/views/user/home.php';
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}