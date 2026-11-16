
<?php

session_start();

$usuario = $_SESSION['usuario'] ?? null;

// --------------------------------------------------
// 1. VALIDAR SESIÓN
// --------------------------------------------------

if (!is_array($usuario)) {
    header('Location: /public/index.php');
    exit;
}

$rol = $usuario['rol'] ?? null;

$rolesPermitidos = ['admin', 'funcionario'];

if (!in_array($rol, $rolesPermitidos, true)) {
    header('Location: /public/index.php');
    exit;
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

        if ($accion === 'eliminar_ticket') {

            $idTicket = (int) ($datos['id_ticket'] ?? 0);

            if ($idTicket <= 0) {
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

        if ($accion !== 'cambiar_estado') {
            $responderJson(400, [
                'success' => false,
                'message' => 'Acción no válida.'
            ]);
        }

        $idTicket = (int) ($datos['id_ticket'] ?? 0);
        $estado = trim($datos['estado'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $encargado = trim($datos['encargado'] ?? '');

        $estadosPermitidos = [
            'Pendiente',
            'En Proceso',
            'Resuelto'
        ];

        if (
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
            'message' => 'Ocurrió un error al actualizar el ticket.'
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

        // Puedes mantener tus vistas actuales:
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

// Si intenta entrar a una vista de otro rol,
// se utiliza la vista inicial de su propio rol.

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

<html lang = "es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte Técnico INSUCO</title>

    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/style.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/dashboard.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modal.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modalState.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/components/modalEliminar.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/navbar.css') ?>">
    <link rel = "stylesheet" href = "<?= versionCSS('/public/css/modalState.css') ?>">

    <link href = "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel = "stylesheet">

    <link rel = "icon" type = "image/png" href = "/public/assets/images/wrench.png">
</head>

<body>

    <?php

    function versionCSS($archivo) {
        $ruta = $_SERVER['DOCUMENT_ROOT'] . $archivo;

        if (file_exists($ruta)) {
            return $archivo . '?v=' . filemtime($ruta);
        }

        return $archivo;
    }
    
    ?>

    <?php require $navbar;?>

    <main class = "contenido">

        <div class = "mainContainer">
            <div class = "contentContainer">
                
                <?php

                    if (!file_exists($pagina)) {
                        die('ERROR: No existe la vista: ' . $pagina);
                    }

                    try {
                        require $pagina;
                    } catch (Throwable $e) {

                    }
                ?>      

            </div>
        </div>
        
    </main>

</body>

</html>