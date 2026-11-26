
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

function actualizarContrasena(int $id, string $hash): bool
{
    global $pdo;

    $stmt = $pdo->prepare(
        'UPDATE usuario SET contrasena_hash = :hash WHERE id = :id'
    );

    return $stmt->execute(['hash' => $hash, 'id' => $id]);
}

function crearUsuario(string $nombre, string $rol, string $correo, string $hash): int
{
    global $pdo;

    $stmt = $pdo->prepare(
        'INSERT INTO usuario (nombre, rol, correo, contrasena_hash)
         VALUES (:nombre, :rol, :correo, :hash)'
    );
    $stmt->execute([
        'nombre' => $nombre,
        'rol' => $rol,
        'correo' => $correo,
        'hash' => $hash
    ]);

    return (int) $pdo->lastInsertId();
}

function listarAdministradores(): array
{
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT id, nombre, correo FROM usuario WHERE rol = 'admin'"
    );
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
