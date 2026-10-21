
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

# Retornadores

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



