
<?php

require_once __DIR__ . '/../../config/database.php';

function listarTickets(): array
{
    global $pdo;

    $sql = 'SELECT
                t.id,
                u.nombre AS funcionario,
                t.lugar,
                t.descripcion,
                t.estado
            FROM ticket t
            INNER JOIN usuario u ON t.id_usuario = u.id
            ORDER BY t.id ASC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarTicketsDeHoy(): int
{
    global $pdo;

    $sql = 'SELECT COUNT(*)
            FROM ticket
            WHERE fecha_creacion >= CURDATE()
              AND fecha_creacion < CURDATE() + INTERVAL 1 DAY';

    $stmt = $pdo->query($sql);

    return (int) $stmt->fetchColumn();
}

function contarTicketsPorEstado(string $estado): int
{
    global $pdo;

    $sql = 'SELECT COUNT(*)
            FROM ticket
            WHERE estado = :estado';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'estado' => $estado
    ]);

    return (int) $stmt->fetchColumn();
}

function actualizarEstadoTicket(
    int $idTicket,
    string $estadoNuevo,
    int $idAutor,
    string $descripcionCambio = '',
    ?string $nuevoEncargado = null
    
): bool {
    global $pdo;

    $estadosPermitidos = [
        'Pendiente',
        'En Proceso',
        'Resuelto'
    ];

    if (
        $idTicket <= 0 ||
        $idAutor <= 0 ||
        !in_array($estadoNuevo, $estadosPermitidos, true)
    ) {
        return false;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SET @id_autor = :id_autor,
                @detalle_cambio = :detalle'
        );

        $stmt->execute([
            'id_autor' => $idAutor,
            'detalle' => $descripcionCambio !== ''
                ? $descripcionCambio
                : null
        ]);

        if ($nuevoEncargado !== null) {
            $stmt = $pdo->prepare(
                'UPDATE ticket
                SET estado = :estado,
                    encargado = :encargado
                WHERE id = :id_ticket'
            );

            $stmt->execute([
                'estado' => $estadoNuevo,
                'encargado' => $nuevoEncargado,
                'id_ticket' => $idTicket
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE ticket
                SET estado = :estado
                WHERE id = :id_ticket'
            );

            $stmt->execute([
                'estado' => $estadoNuevo,
                'id_ticket' => $idTicket
            ]);
        }

        $cambioRealizado = $stmt->rowCount() > 0;

        $pdo->commit();

        return $cambioRealizado;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;

    } finally {
        try {
            $pdo->exec(
                'SET @id_autor = NULL, @detalle_cambio = NULL'
            );
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}

#####################################################################
# Retornadores
#####################################################################

function obtenerTicketsDeHoy(): int
{
    return contarTicketsDeHoy();
}

function listarTicketsAbiertos(): int
{
    return contarTicketsPorEstado('Pendiente');
}

function listarTicketsEnProceso(): int
{
    return contarTicketsPorEstado('En Proceso');
}

function listarTicketsResueltos(): int
{
    return contarTicketsPorEstado('Resuelto');
}

#####################################################################
# Historial Tickets
#####################################################################

function obtenerHistorialTicket(int $idTicket): array
{
    global $pdo;

    $sql = '
        SELECT
            h.id,
            h.id_ticket,
            h.id_autor,
            u.nombre AS autor,
            t.encargado,
            h.tipo_cambio,
            h.estado_anterior,
            h.estado_nuevo,
            h.detalle,
            h.fecha_cambio
        FROM historial_ticket h
        INNER JOIN usuario u ON u.id = h.id_autor
        LEFT JOIN ticket t ON t.id = h.id_ticket
        WHERE h.id_ticket = :id_ticket
        ORDER BY h.fecha_cambio DESC, h.id DESC
    ';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_ticket' => $idTicket
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
