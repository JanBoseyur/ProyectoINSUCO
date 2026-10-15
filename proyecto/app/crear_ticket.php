
<?php

require_once __DIR__ . '/../config/database.php';

$id_usuario = 6;
$descripcion = 'Computador no enciende';
$lugar = 'PIE';
$estado = 'Pendiente';

try {
    $sql = 'INSERT INTO ticket
                (id_usuario, descripcion, lugar, estado, fecha_creacion, fecha_cierre)
            VALUES
                (:id_usuario, :descripcion, :lugar, :estado, CURRENT_TIMESTAMP, NULL)';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id_usuario' => $id_usuario,
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