
<?php

require_once __DIR__ . '/../../config/database.php';

function buscarUsuarioPorCorreo(string $correo): array|false

{
    global $pdo;

    $sql = 'SELECT id, nombre, rol, correo, contrasena_hash
            FROM usuario
            WHERE correo = :correo
            LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['correo' => $correo]);

    return $stmt->fetch();
}
