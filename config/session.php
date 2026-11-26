
<?php

// --------------------------------------------------
// 1. CONFIGURACIÓN DE SESIONES
// --------------------------------------------------

// Evitar iniciar la sesión más de una vez.
if (session_status() === PHP_SESSION_NONE) {

    // Configuración de seguridad.
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');

    // Configuración de cookies.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // true si utilizas HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Iniciar sesión.
    session_start();
}

// --------------------------------------------------
// 2. INICIAR SESIÓN
// --------------------------------------------------

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --------------------------------------------------
// 3. VERIFICAR TIEMPO DE INACTIVIDAD
// --------------------------------------------------

function verificarTiempoSesion(): void
{
    $tiempoMaximo = 1800; // 30 minutos.

    if (!isset($_SESSION['usuario'])) {
        return;
    }

    $ultimaActividad = $_SESSION['ultima_actividad'] ?? time();

    if ((time() - $ultimaActividad) > $tiempoMaximo) {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite']
            ]);
        }

        session_destroy();

        header('Location: /public/index.php?sesion=expirada');
        exit;
    }

    $_SESSION['ultima_actividad'] = time();
}
