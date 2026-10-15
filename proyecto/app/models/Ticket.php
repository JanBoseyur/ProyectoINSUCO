
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
            ORDER BY t.id DESC';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}