
<?php

require_once __DIR__ . '/../config/database.php';

$nombre = 'Profesor de Prueba';
$rol = 'usuario';
$correo = 'profe@gmail.com';
$contrasena = 'Profe123';

$contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

try {
    $sql = 'INSERT INTO usuario (nombre, rol, correo, contrasena_hash)
            VALUES (:nombre, :rol, :correo, :contrasena_hash)';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'nombre' => $nombre,
        'rol' => $rol,
        'correo' => $correo,
        'contrasena_hash' => $contrasenaHash
    ]);

    echo 'Usuario de prueba creado correctamente.';

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo 'Ese correo ya está registrado. Puedes utilizar el usuario existente.';

    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo 'No se pudo crear el usuario.';
    }
}
