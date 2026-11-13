
<?php

require_once __DIR__ . '/../../config/database.php';

function listarTickets(): array
{
    global $pdo;

    $sql = 'SELECT
                t.id,
                u.nombre AS funcionario,
                t.encargado,
                t.lugar,
                t.descripcion,
                t.estado
            FROM ticket t
            INNER JOIN usuario u ON t.id_usuario = u.id
            ORDER BY t.id ASC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertarTicket(
    int $idUsuario,
    string $lugar,
    string $descripcion
): int {
    global $pdo;

    $lugar = trim($lugar);
    $descripcion = trim($descripcion);

    if (
        $idUsuario <= 0 ||
        $lugar === '' ||
        $lugar === 'Otra ubicación' ||
        mb_strlen($lugar) > 150 ||
        $descripcion === '' ||
        mb_strlen($descripcion) > 2000
    ) {
        throw new InvalidArgumentException('Los datos del ticket no son válidos.');
    }

    $sql = 'INSERT INTO ticket
            (id_usuario, encargado, lugar, descripcion)
            VALUES
            (:id_usuario, :encargado, :lugar, :descripcion)';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_usuario' => $idUsuario,
        'encargado' => 'Sin asignar',
        'lugar' => $lugar,
        'descripcion' => $descripcion
    ]);

    return (int) $pdo->lastInsertId();
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

    $descripcionCambio = trim($descripcionCambio);

    if ($nuevoEncargado !== null) {
        $nuevoEncargado = trim($nuevoEncargado);
    }

    if (
        $idTicket <= 0 ||
        $idAutor <= 0 ||
        !in_array($estadoNuevo, $estadosPermitidos, true) ||
        mb_strlen($descripcionCambio) > 1000 ||
        ($nuevoEncargado !== null &&
            ($nuevoEncargado === '' || mb_strlen($nuevoEncargado) > 150))
    ) {
        return false;
    }

    try {
        $pdo->beginTransaction();

        // 1. Obtener los datos actuales del ticket.
        $stmt = $pdo->prepare(
            'SELECT estado, encargado
             FROM ticket
             WHERE id = :id
             FOR UPDATE'
        );

        $stmt->execute(['id' => $idTicket]);

        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            $pdo->rollBack();
            return false;
        }

        $estadoAnterior = $ticket['estado'];
        $encargadoAnterior = $ticket['encargado'];

        // 2. Determinar los nuevos valores.
        $encargadoFinal = $nuevoEncargado !== null
            ? $nuevoEncargado
            : $encargadoAnterior;

        $cambioEstado = $estadoAnterior !== $estadoNuevo;
        $cambioEncargado = $encargadoAnterior !== $encargadoFinal;

        // Si no hubo cambios, no registrar nada.
        if (!$cambioEstado && !$cambioEncargado) {
            $pdo->commit();
            return false;
        }

        // 3. Actualizar el ticket.
        $stmt = $pdo->prepare(
            'UPDATE ticket
             SET estado = :estado,
                 encargado = :encargado,
                 fecha_actualizacion = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $stmt->execute([
            'estado' => $estadoNuevo,
            'encargado' => $encargadoFinal,
            'id' => $idTicket
        ]);

        // 4. Construir el detalle del historial.
        $detalles = [];

        if ($descripcionCambio !== '') {
            $detalles[] = $descripcionCambio;
        }

        if ($cambioEncargado) {
            $detalles[] = 'Encargado cambiado de "' .
                $encargadoAnterior . '" a "' .
                $encargadoFinal . '".';
        }

        $detalleFinal = !empty($detalles)
            ? implode(' ', $detalles)
            : 'Actualización realizada desde la plataforma.';

        // 5. Determinar el tipo de cambio.
        if ($cambioEstado && $cambioEncargado) {
            $tipoCambio = 'Cambio de estado y encargado';
        } elseif ($cambioEstado) {
            $tipoCambio = 'Cambio de estado';
        } else {
            $tipoCambio = 'Cambio de encargado';
        }

        // 6. Registrar el historial con las nuevas columnas.
        $sql = "INSERT INTO historial_ticket (
                    id_ticket,
                    id_autor,
                    tipo_cambio,
                    estado_anterior,
                    estado_nuevo,

                    detalle
                ) VALUES (
                    :id_ticket,
                    :id_autor,
                    :tipo_cambio,
                    :estado_anterior,
                    :estado_nuevo,
                    :detalle
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'id_ticket' => $idTicket,
            'id_autor' => $idAutor,
            'tipo_cambio' => $tipoCambio,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'detalle' => $detalleFinal
        ]);

        // 7. Confirmar las operaciones.
        $pdo->commit();

        return true;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('Error al actualizar ticket: ' . $e->getMessage());

        return false;
    }
}

function listarTicketsPorUsuario(int $idUsuario): array
{
    global $pdo;

    $sql = 'SELECT
                t.id,
                u.nombre AS funcionario,
                t.encargado,
                t.lugar,
                t.descripcion,
                t.estado
            FROM ticket t
            INNER JOIN usuario u ON t.id_usuario = u.id
            WHERE t.id_usuario = :id_usuario
            ORDER BY t.id DESC';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_usuario' => $idUsuario
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

function eliminarTicket(int $idTicket): bool
{
    global $pdo;

    try {

        $pdo->beginTransaction();

        // Eliminar primero el historial asociado
        $sqlHistorial = "
            DELETE FROM historial_ticket
            WHERE id_ticket = :id
        ";

        $stmt = $pdo->prepare($sqlHistorial);
        $stmt->execute(['id' => $idTicket]);

        // Eliminar el ticket
        $sqlTicket = "
            DELETE FROM ticket
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sqlTicket);
        $stmt->execute(['id' => $idTicket]);

        // Comprobar que realmente se eliminó
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();

        return true;

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        return false;
    }
}

function buscarTickets(string $termino): array
{
    global $pdo;

    $sql = "
        SELECT *
        FROM ticket
        WHERE encargado LIKE :busqueda
           OR lugar LIKE :busqueda
           OR descripcion LIKE :busqueda
        ORDER BY id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':busqueda' => '%' . $termino . '%'
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
