<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/PageController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../helpers/assets.php';

function obtenerRutasPagina(): array
{
    return [
        'admin' => [
            'dashboard' => __DIR__ . '/../views/admin/dashboard.php',
            'configuracion' => __DIR__ . '/../views/admin/management.php'
        ],
        'funcionario' => [
            'home' => __DIR__ . '/../views/user/home.php',
            'misTickets' => __DIR__ . '/../views/user/misTickets.php'
        ]
    ];
}

function obtenerScriptsPagina(string $seccion): array
{
    return match ($seccion) {
        'dashboard' => [
            '/public/js/modal.js',
            '/public/js/modalEstado.js',
            '/public/js/modalConfirmacion.js',
            '/public/js/filtroEstado.js',
            '/public/js/descripcionTicket.js'
        ],
        'configuracion' => ['/public/js/modalConfirmacion.js'],
        'home' => [
            '/public/js/modalNotificacion.js',
            '/public/js/filtroEstado.js'
        ],
        'misTickets' => [
            '/public/js/filtroEstado.js',
            '/public/js/editarTicket.js',
            '/public/js/descripcionTicket.js'
        ],
        default => []
    };
}

function despacharSolicitudAplicacion(): array
{
    verificarTiempoSesion();

    $usuario = $_SESSION['usuario'] ?? null;
    $seccionSolicitada = (string) ($_GET['seccion'] ?? '');

    if (!is_array($usuario)) {
        if ($seccionSolicitada === 'registro') {
            $mensaje = $_SERVER['REQUEST_METHOD'] === 'POST' ? procesarRegistro() : '';
            return [
                'tipo' => 'auth',
                'vista' => __DIR__ . '/../views/auth/register.php',
                'datos' => compact('mensaje')
            ];
        }

        if ($seccionSolicitada === 'cambiar-contrasena') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $error = procesarCambioContrasena();
            $csrfToken = $_SESSION['csrf_token'];
            return [
                'tipo' => 'auth',
                'vista' => __DIR__ . '/../views/auth/cambiarContrasena.php',
                'datos' => compact('error', 'csrfToken')
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            procesarLogin();
        }

        $mensaje = isset($_GET['error'])
            ? 'El correo o la contraseña no son correctos.'
            : (isset($_GET['contrasena']) ? 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.' : '');

        return [
            'tipo' => 'auth',
            'vista' => __DIR__ . '/../views/auth/login.php',
            'datos' => compact('mensaje')
        ];
    }

    $rol = (string) ($usuario['rol'] ?? '');
    if (!in_array($rol, ['admin', 'funcionario'], true)) {
        http_response_code(403);
        exit('No tienes permiso para acceder.');
    }

    $secciones = obtenerRutasPagina();
    $seccion = $seccionSolicitada !== ''
        ? $seccionSolicitada
        : ($rol === 'admin' ? 'dashboard' : 'home');

    if (!isset($secciones[$rol][$seccion])) {
        $seccion = $rol === 'admin' ? 'dashboard' : 'home';
    }

    $datos = obtenerDatosSeccion($seccion, $usuario);
    $datos['usuario'] = $usuario;
    $datos['rol'] = $rol;
    $datos['seccion'] = $seccion;
    $datos['rutaLayout'] = '/public/index.php';
    $datos['pagina'] = $secciones[$rol][$seccion];
    $datos['navbar'] = $rol === 'admin'
        ? __DIR__ . '/../views/components/navbarAdmin.php'
        : __DIR__ . '/../views/components/navbarFuncionario.php';
    $datos['scriptsPagina'] = obtenerScriptsPagina($seccion);

    return [
        'tipo' => 'app',
        'vista' => __DIR__ . '/../views/layout/main.php',
        'datos' => $datos
    ];
}
