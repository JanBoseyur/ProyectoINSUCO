
<?php

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../services/notificacionService.php';

function obtenerAdministradores(): array
{
    return listarAdministradores();
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
    int $idLugar,
    string $descripcion,
    string $categoria
): int {
    $idTicket = insertarTicket(
        $idUsuario,
        $idLugar,
        $descripcion,
        $categoria
    );

    notificarAdministradoresNuevoTicket($idTicket);

    return $idTicket;
}

function notificarAdministradoresNuevoTicket(int $idTicket): void
{
    global $pdo;

    try {
        $stmt = $pdo->prepare(
            'SELECT t.id, t.descripcion, t.categoria, t.estado, t.fecha_creacion,
                    u.nombre AS solicitante, u.correo AS correo_solicitante,
                    d.nombre AS lugar
             FROM ticket t
             INNER JOIN usuario u ON u.id = t.id_usuario
             INNER JOIN dependencias d ON d.id = t.id_lugar
             WHERE t.id = :id_ticket'
        );
        $stmt->execute(['id_ticket' => $idTicket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) {
            return;
        }

        $configCorreo = NotificacionService::configuracionDesdeEntorno();
        $solicitante = htmlspecialchars((string) $ticket['solicitante'], ENT_QUOTES, 'UTF-8');
        $correoSolicitante = htmlspecialchars((string) $ticket['correo_solicitante'], ENT_QUOTES, 'UTF-8');
        $lugar = htmlspecialchars((string) $ticket['lugar'], ENT_QUOTES, 'UTF-8');
        $categoria = htmlspecialchars((string) $ticket['categoria'], ENT_QUOTES, 'UTF-8');
        $descripcion = nl2br(htmlspecialchars((string) $ticket['descripcion'], ENT_QUOTES, 'UTF-8'));
        $fecha = htmlspecialchars((string) $ticket['fecha_creacion'], ENT_QUOTES, 'UTF-8');
        $asunto = 'Nuevo ticket de soporte #' . $idTicket;
        $mensaje = '<p>Se ha creado un nuevo ticket de soporte.</p>'
            . '<p><strong>Ticket:</strong> #' . $idTicket . '<br>'
            . '<strong>Solicitante:</strong> ' . $solicitante . ' (' . $correoSolicitante . ')<br>'
            . '<strong>Lugar:</strong> ' . $lugar . '<br>'
            . '<strong>Categoría:</strong> ' . $categoria . '<br>'
            . '<strong>Estado:</strong> ' . htmlspecialchars((string) $ticket['estado'], ENT_QUOTES, 'UTF-8') . '<br>'
            . '<strong>Fecha:</strong> ' . $fecha . '</p>'
            . '<p><strong>Descripción:</strong><br>' . $descripcion . '</p>';

        foreach (obtenerAdministradores() as $administrador) {
            $destinatario = trim((string) ($administrador['correo'] ?? ''));
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            try {
                (new NotificacionService($pdo, $configCorreo))->enviar(
                    (int) $administrador['id'],
                    $idTicket,
                    'nuevo_ticket_admin',
                    $destinatario,
                    $asunto,
                    $mensaje
                );
            } catch (Throwable $e) {
                error_log('No se pudo notificar al administrador sobre el ticket #' . $idTicket . ': ' . $e->getMessage());
            }
        }
    } catch (Throwable $e) {
        // El ticket ya fue creado; una falla de correo no debe invalidarlo.
        error_log('Error al preparar notificaciones para el nuevo ticket #' . $idTicket . ': ' . $e->getMessage());
    }
}

function editarTicketFuncionario(
    int $idTicket,
    int $idUsuario,
    int $idLugar,
    string $categoria,
    string $descripcion
): bool {
    return actualizarTicketFuncionario(
        $idTicket,
        $idUsuario,
        $idLugar,
        $categoria,
        $descripcion
    );
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

function fechaTicketEnEspanol(string $fecha): string
{
    $momento = new DateTimeImmutable($fecha);
    $dias = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
    ];
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    return sprintf(
        '%s %d de %s a las %s hrs',
        $dias[(int) $momento->format('N')],
        (int) $momento->format('j'),
        $meses[(int) $momento->format('n')],
        $momento->format('H:i')
    );
}

function obtenerLugaresActivos(): array
{
    return listarLugaresActivos();
}

function agregarDependenciaActiva(string $nombre): int
{
    return agregarDependencia($nombre);
}

function desactivarDependencia(int $idDependencia): bool
{
    return eliminarDependencia($idDependencia);
}

function obtenerCategoriasActivas(): array
{
    return listarCategorias();
}

function desactivarCategoria(int $idCategoria): bool
{
    return eliminarCategoria($idCategoria);
}
