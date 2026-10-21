
<?php

require_once __DIR__ . '/../config/database.php';

$id_usuario = 6;
$encargado = 'Sebastián Muñoz';
$descripcion = 'Computador sin imagen';
$lugar = 'Sala 162';
$estado = 'Pendiente';

try {
    $sql = 'INSERT INTO ticket
                (id_usuario, encargado, descripcion, lugar, estado)
            VALUES
                (:id_usuario, :encargado, :descripcion, :lugar, :estado)';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_usuario' => $id_usuario,
        'encargado' => $encargado,
        'descripcion' => $descripcion,
        'lugar' => $lugar,
        'estado' => $estado
    ]);

    echo 'Ticket creado correctamente.';

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'No se pudo crear el ticket.';
}
