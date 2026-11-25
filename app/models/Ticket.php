
<?php

require_once __DIR__ . '/../../config/database.php';

function listarTickets(): array
{
    global $pdo;

    $sql = 'SELECT
                t.id,
                u.nombre AS funcionario,
                t.encargado,
                l.nombre AS lugar,
                t.descripcion,
                t.estado,
                t.fecha_creacion
            FROM ticket t
            INNER JOIN usuario u ON t.id_usuario = u.id
            INNER JOIN dependencias l ON t.id_lugar = l.id
            ORDER BY t.id ASC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertarTicket(
    int $idUsuario,
    int $idLugar,
    string $descripcion,
    string $categoria
): int {
    global $pdo;

    $descripcion = trim($descripcion);
    $categoria = trim($categoria);

    if (
        $idUsuario <= 0 ||
        $idLugar <= 0 ||
        !categoriaActivaExiste($categoria) ||
        $descripcion === '' ||
        mb_strlen($descripcion) > 2000
    ) {
        throw new InvalidArgumentException(
            'Los datos del ticket no son válidos.'
        );
    }

    // Comprobar que el lugar exista y esté activo.
    $sqlLugar = 'SELECT id
                 FROM dependencias
                 WHERE id = :id
                 AND activo = 1';

    $stmtLugar = $pdo->prepare($sqlLugar);
    $stmtLugar->execute(['id' => $idLugar]);

    if (!$stmtLugar->fetchColumn()) {
        throw new InvalidArgumentException(
            'El lugar seleccionado no existe o está inactivo.'
        );
    }

    // Registrar el ticket.
    $sql = 'INSERT INTO ticket (
                id_usuario,
                id_lugar,
                categoria,
                encargado,
                descripcion
            ) VALUES (
                :id_usuario,
                :id_lugar,
                :categoria,
                :encargado,
                :descripcion
            )';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_usuario' => $idUsuario,
        'id_lugar' => $idLugar,
        'categoria' => $categoria,
        'encargado' => 'Sin asignar',
        'descripcion' => $descripcion
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarTicketFuncionario(
    int $idTicket,
    int $idUsuario,
    int $idLugar,
    string $categoria,
    string $descripcion
): bool {
    global $pdo;

    $categoria = trim($categoria);
    $descripcion = trim($descripcion);

    if (
        $idTicket <= 0 ||
        $idUsuario <= 0 ||
        $idLugar <= 0 ||
        !categoriaActivaExiste($categoria) ||
        $descripcion === '' ||
        mb_strlen($descripcion) > 1000
    ) {
        return false;
    }

    $sql = 'UPDATE ticket AS t
            INNER JOIN dependencias AS d ON d.id = :id_lugar AND d.activo = 1
            SET t.id_lugar = :id_lugar_set,
                t.categoria = :categoria,
                t.descripcion = :descripcion
            WHERE t.id = :id_ticket
              AND t.id_usuario = :id_usuario';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_lugar' => $idLugar,
        'id_lugar_set' => $idLugar,
        'categoria' => $categoria,
        'descripcion' => $descripcion,
        'id_ticket' => $idTicket,
        'id_usuario' => $idUsuario
    ]);

    if ($stmt->rowCount() > 0) {
        return true;
    }

    $stmt = $pdo->prepare(
        'SELECT 1 FROM ticket WHERE id = :id_ticket AND id_usuario = :id_usuario'
    );
    $stmt->execute([
        'id_ticket' => $idTicket,
        'id_usuario' => $idUsuario
    ]);

    return (bool) $stmt->fetchColumn();
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
        'En proceso',
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

    $sql = "SELECT
                t.id,
                t.id_lugar,
                u.nombre AS funcionario,
                t.encargado,
                l.nombre AS lugar,
                t.descripcion,
                t.estado,
                t.categoria,
                t.fecha_creacion
            FROM ticket t
            INNER JOIN usuario u ON t.id_usuario = u.id
            INNER JOIN dependencias l ON t.id_lugar = l.id
            WHERE t.id_usuario = :id_usuario
            ORDER BY t.id DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_usuario' => $idUsuario
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

    $sql = '
        SELECT
            t.id,
            t.id_usuario,
            u.nombre AS funcionario,
            t.encargado,
            l.nombre AS lugar,
            t.descripcion,
            t.estado
        FROM ticket t
        INNER JOIN usuario u ON t.id_usuario = u.id
        INNER JOIN dependencias l ON t.id_lugar = l.id
        WHERE t.encargado LIKE :busqueda
           OR l.nombre LIKE :busqueda
           OR t.descripcion LIKE :busqueda
           OR u.nombre LIKE :busqueda
        ORDER BY t.id DESC
    ';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'busqueda' => '%' . trim($termino) . '%'
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarLugaresActivos(): array
{
    global $pdo;

    $sql = 'SELECT id, nombre
            FROM dependencias
            WHERE activo = 1
            ORDER BY nombre ASC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function agregarDependencia(string $nombre): int
{
    global $pdo;

    $nombre = trim($nombre);
    if ($nombre === '' || mb_strlen($nombre) > 100) {
        throw new InvalidArgumentException('El nombre de la dependencia no es válido.');
    }

    $stmt = $pdo->prepare(
        'SELECT id, activo FROM dependencias WHERE LOWER(nombre) = LOWER(:nombre) LIMIT 1'
    );
    $stmt->execute(['nombre' => $nombre]);
    $existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existente && (int) $existente['activo'] === 1) {
        throw new InvalidArgumentException('Ya existe una dependencia con ese nombre.');
    }

    if ($existente) {
        $stmt = $pdo->prepare('UPDATE dependencias SET activo = 1 WHERE id = :id');
        $stmt->execute(['id' => $existente['id']]);
        return (int) $existente['id'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO dependencias (nombre, activo) VALUES (:nombre, 1)'
    );
    $stmt->execute(['nombre' => $nombre]);

    return (int) $pdo->lastInsertId();
}

function eliminarDependencia(int $idDependencia): bool
{
    global $pdo;

    if ($idDependencia <= 0) {
        return false;
    }

    $stmt = $pdo->prepare(
        'UPDATE dependencias SET activo = 0 WHERE id = :id AND activo = 1'
    );
    $stmt->execute(['id' => $idDependencia]);

    return $stmt->rowCount() > 0;
}

function listarCategorias(): array
{
    global $pdo;

    $sql = 'SELECT id, nombre
            FROM categoria
            WHERE activo = 1
            ORDER BY nombre ASC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function agregarCategoria(string $nombre): int
{
    global $pdo;

    $nombre = trim($nombre);
    if ($nombre === '' || mb_strlen($nombre) > 100) {
        throw new InvalidArgumentException('El nombre de categoría no es válido.');
    }

    $stmt = $pdo->prepare(
        'SELECT id, activo FROM categoria WHERE LOWER(nombre) = LOWER(:nombre) LIMIT 1'
    );
    $stmt->execute(['nombre' => $nombre]);
    $existente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existente && (int) $existente['activo'] === 1) {
        throw new InvalidArgumentException('Ya existe una categoría con ese nombre.');
    }

    if ($existente) {
        $stmt = $pdo->prepare('UPDATE categoria SET activo = 1 WHERE id = :id');
        $stmt->execute(['id' => $existente['id']]);
        return (int) $existente['id'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO categoria (nombre, activo)
         VALUES (:nombre, 1)'
    );

    $stmt->execute(['nombre' => $nombre]);

    return (int) $pdo->lastInsertId();
}

function eliminarCategoria(int $idCategoria): bool
{
    global $pdo;

    if ($idCategoria <= 0) {
        return false;
    }

    $stmt = $pdo->prepare(
        'UPDATE categoria SET activo = 0 WHERE id = :id AND activo = 1'
    );
    $stmt->execute(['id' => $idCategoria]);

    return $stmt->rowCount() > 0;
}

function categoriaActivaExiste(string $nombre): bool
{
    global $pdo;

    if ($nombre === '') {
        return false;
    }

    $stmt = $pdo->prepare(
        'SELECT 1 FROM categoria WHERE nombre = :nombre AND activo = 1 LIMIT 1'
    );
    $stmt->execute(['nombre' => $nombre]);

    return (bool) $stmt->fetchColumn();
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
    return contarTicketsPorEstado('En proceso');
}

function listarTicketsResueltos(): int
{
    return contarTicketsPorEstado('Resuelto');
}

#####################################################################
# Historial Tickets
#####################################################################
