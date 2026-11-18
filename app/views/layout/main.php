
<?php

function versionCSS(string $archivo): string
{
    $ruta = $_SERVER['DOCUMENT_ROOT'] . $archivo;

    if (file_exists($ruta)) {
        return $archivo . '?v=' . filemtime($ruta);
    }

    return $archivo;
}

require_once __DIR__ . '/../../../config/session.php';

verificarTiempoSesion();

$usuario = $_SESSION['usuario'] ?? null;

// --------------------------------------------------
// 1. VALIDAR SESIÓN Y ROL
// --------------------------------------------------

if (!is_array($usuario)) {
    header('Location: /public/index.php');
    exit;
}

$rol = $usuario['rol'] ?? null;

$rolesPermitidos = ['admin', 'funcionario'];

if (!in_array($rol, $rolesPermitidos, true)) {
    http_response_code(403);
    exit('No tienes permiso para acceder.');
}

// --------------------------------------------------
// 2. RESPUESTA JSON
// --------------------------------------------------

$responderJson = function (int $codigo, array $datos): void {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
};

$esSolicitudJson =
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    str_contains(
        $_SERVER['CONTENT_TYPE'] ?? '',
        'application/json'
    );

// --------------------------------------------------
// 3. PROCESAMIENTO AJAX (SOLO ADMIN)
// --------------------------------------------------

if ($esSolicitudJson) {

    if ($rol !== 'admin') {
        $responderJson(403, [
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción.'
        ]);
    }

    require_once __DIR__ . '/../../controllers/TicketController.php';

    try {

        $datos = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($datos)) {
            $responderJson(400, [
                'success' => false,
                'message' => 'Datos inválidos.'
            ]);
        }

        $accion = $datos['accion'] ?? '';

        // ------------------------------------------
        // ELIMINAR TICKET
        // ------------------------------------------

        if ($accion === 'eliminar_ticket') {

            $idTicket = filter_var(
                $datos['id_ticket'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$idTicket || $idTicket <= 0) {
                $responderJson(400, [
                    'success' => false,
                    'message' => 'ID de ticket inválido.'
                ]);
            }

            $resultado = eliminarTicket($idTicket);

            if (!$resultado) {
                $responderJson(400, [
                    'success' => false,
                    'message' => 'No se pudo eliminar el ticket.'
                ]);
            }

            $responderJson(200, [
                'success' => true,
                'message' => 'Ticket eliminado correctamente.'
            ]);
        }

        // ------------------------------------------
        // CAMBIAR ESTADO
        // ------------------------------------------

        if ($accion !== 'cambiar_estado') {
            $responderJson(400, [
                'success' => false,
                'message' => 'Acción no válida.'
            ]);
        }

        $idTicket = filter_var(
            $datos['id_ticket'] ?? null,
            FILTER_VALIDATE_INT
        );

        $estado = trim($datos['estado'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $encargado = trim($datos['encargado'] ?? '');

        $estadosPermitidos = [
            'Pendiente',
            'En proceso',
            'Resuelto'
        ];

        if (
            !$idTicket ||
            $idTicket <= 0 ||
            !in_array($estado, $estadosPermitidos, true) ||
            mb_strlen($descripcion) > 1000 ||
            mb_strlen($encargado) > 150
        ) {
            $responderJson(400, [
                'success' => false,
                'message' => 'Los datos enviados no son válidos.'
            ]);
        }

        $resultado = cambiarEstado(
            $idTicket,
            $estado,
            (int) $usuario['id'],
            $descripcion,
            $encargado !== '' ? $encargado : null
        );

        if (!$resultado) {
            $responderJson(400, [
                'success' => false,
                'message' => 'No fue posible actualizar el ticket.'
            ]);
        }

        $responderJson(200, [
            'success' => true,
            'message' => 'El estado del ticket se actualizó correctamente.'
        ]);

    } catch (Throwable $e) {

        error_log($e->getMessage());

        $responderJson(500, [
            'success' => false,
            'message' => 'Ocurrió un error al procesar la solicitud.'
        ]);
    }
}

// --------------------------------------------------
// 4. CONFIGURACIÓN DE VISTAS POR ROL
// --------------------------------------------------

$secciones = [

    'admin' => [
        'dashboard' => __DIR__ . '/../admin/dashboard.php',
        'tickets' => __DIR__ . '/../admin/tickets.php',
        'usuarios' => __DIR__ . '/../admin/usuarios.php',
        'configuracion' => __DIR__ . '/../admin/configuracion.php',
        'a' => __DIR__ . '/../admin/a.php',
        'b' => __DIR__ . '/../admin/b.php',
        'c' => __DIR__ . '/../admin/c.php',
    ],

    'funcionario' => [
        'home' => __DIR__ . '/../user/home.php',
        'misTickets' => __DIR__ . '/../user/misTickets.php',
    ]

];

// --------------------------------------------------
// 5. SELECCIONAR VISTA
// --------------------------------------------------

$seccion = $_GET['seccion'] ?? (
    $rol === 'admin' ? 'dashboard' : 'home'
);

if (!isset($secciones[$rol][$seccion])) {
    $seccion = $rol === 'admin' ? 'dashboard' : 'home';
}

$pagina = $secciones[$rol][$seccion];

// --------------------------------------------------
// 6. SELECCIONAR NAVBAR
// --------------------------------------------------

$navbar = $rol === 'admin'
    ? __DIR__ . '/../components/navbarAdmin.php'
    : __DIR__ . '/../components/navbarFuncionario.php';

?>

<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Soporte Técnico INSUCO</title>

    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/dashboard.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/misTickets.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modal.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modalState.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modalEliminar.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/navbar.css') ?>">

    <link href = "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 
    <link rel ="icon" type = "image/png" href = "/public/assets/images/wrench.png">
</head>

<body>

    <?php require $navbar; ?>

    <main class = "contenido">

        <div class = "mainContainer">
            <div class = "contentContainer">

                <?php

                if (!file_exists($pagina)) {
                    error_log('No existe la vista: ' . $pagina);
                    http_response_code(500);
                    exit('No se pudo cargar la página.');
                }

                try {
                    require $pagina;
                } catch (Throwable $e) {

                    error_log(
                        'Error al cargar la vista: ' . $e->getMessage()
                    );

                    http_response_code(500);
                    echo 'Ocurrió un error al cargar la página.';
                }

                ?>

            </div>
        </div>

    </main>

</body>

</html>
