
<?php

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../../config/database.php';

function obtenerAdministradores(): array
{
    global $pdo;

    $sql = "SELECT id, nombre, correo
            FROM usuario
            WHERE rol = 'admin'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

# Funciones Administrativas
function obtenerTickets(): array
{
    return listarTickets();
}

function obtenerTicketsAbiertos(): int
{
    return listarTicketsAbiertos();
}

function obtenerTicketsEnProceso(): int
{
    return listarTicketsEnProceso();
}

function obtenerTicketsResueltos(): int
{
    return listarTicketsResueltos();
}

function obtenerTicketsFuncionario(int $idUsuario): array
{
    return listarTicketsPorUsuario($idUsuario);
}

function cambiarEstado(
    int $idTicket,
    string $estadoNuevo,
    int $idAutor,
    string $descripcionCambio = '',
    ?string $nuevoEncargado = null

): bool {
    
    return actualizarEstadoTicket(
        $idTicket,
        $estadoNuevo,
        $idAutor,
        $descripcionCambio,
        $nuevoEncargado
    );
}

function registrarTicket(
    int $idUsuario,
    string $lugar,
    string $descripcion
): int {
    return insertarTicket($idUsuario, $lugar, $descripcion);
}

# Funciones de Usuario
function procesarCreacionTicket(): void
{
    global $notificador;

    $usuario = $_SESSION['usuario'] ?? null;

    if (
        !is_array($usuario) ||
        ($usuario['rol'] ?? null) !== 'funcionario'
    ) {
        http_response_code(403);
        exit('No tienes permiso para realizar esta acción.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        empty($_SESSION['csrf_ticket']) ||
        !hash_equals($_SESSION['csrf_ticket'], $token)
    ) {
        $_SESSION['notificacion'] = [
            'tipo' => 'error',
            'titulo' => 'Error de validación',
            'mensaje' => 'No se pudo validar el formulario.'
        ];

        return;
    }

    try {

        $lugar = trim($_POST['lugar'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        // 1. Registrar ticket

        $idTicket = registrarTicket(
            (int) $usuario['id'],
            $lugar,
            $descripcion
        );

        // 2. Obtener administradores

        try {

            $administradores = obtenerAdministradores();

            // Escapar contenido dinámico para HTML

            $nombreFuncionario = htmlspecialchars(
                $usuario['nombre'] ?? 'Funcionario',
                ENT_QUOTES,
                'UTF-8'
            );

            $lugarHTML = htmlspecialchars(
                $lugar,
                ENT_QUOTES,
                'UTF-8'
            );

            $descripcionHTML = nl2br(
                htmlspecialchars(
                    $descripcion,
                    ENT_QUOTES,
                    'UTF-8'
                )
            );

            // 3. Preparar correo

            $asunto = "Nuevo ticket registrado #{$idTicket}";

            $mensaje = "
                <h2>Nuevo ticket registrado</h2>

                <p>Se ha registrado una nueva solicitud de soporte técnico.</p>

                <hr>

                <p><strong>Ticket:</strong> #{$idTicket}</p>

                <p><strong>Funcionario:</strong> {$nombreFuncionario}</p>

                <p><strong>Lugar:</strong> {$lugarHTML}</p>

                <p><strong>Descripción:</strong></p>

                <p>{$descripcionHTML}</p>

                <hr>

                <p>
                    Ingresa a la plataforma INSUCO para revisar
                    y gestionar esta solicitud.
                </p>
            ";

            // 4. Enviar correo a cada administrador

            foreach ($administradores as $admin) {

                try {

                    $notificador->enviar(
                        (int) $admin['id'],
                        $idTicket,
                        'nuevo_ticket',
                        $admin['correo'],
                        $asunto,
                        $mensaje
                    );

                } catch (Throwable $e) {

                    error_log(
                        'Error al registrar notificación para administrador '
                        . $admin['id'] . ': '
                        . $e->getMessage()
                    );
                }
            }

        } catch (Throwable $e) {

            // El error de notificación no debe impedir
            // que el ticket se considere creado.

            error_log(
                'Error en el sistema de notificaciones: '
                . $e->getMessage()
            );
        }

        // 5. Confirmar creación del ticket

        $_SESSION['notificacion'] = [
            'tipo' => 'exito',
            'titulo' => '¡Ticket creado con éxito!',
            'mensaje' => 'El ticket ha sido registrado correctamente.'
        ];

        $_SESSION['csrf_ticket'] = bin2hex(random_bytes(32));

    } catch (Throwable $e) {

        error_log($e->getMessage());

        $_SESSION['notificacion'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'mensaje' => 'No fue posible registrar el ticket.'
        ];
    }
}
