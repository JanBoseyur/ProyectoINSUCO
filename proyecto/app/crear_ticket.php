
<?php

require_once __DIR__ . '/../config/database.php';

$encargados = [
    'Sebastián Muñoz',
    'Juan Pérez',
    'María González',
    'Camila Soto',
    'Pedro Ramírez'
];  

$descripciones = [
    'Computador sin imagen',
    'Teclado no funciona',
    'Mouse defectuoso',
    'Problemas de conexión a Internet',
    'Impresora no imprime',
    'Monitor parpadea',
    'Computador lento',
    'Problemas con el sistema operativo',
    'Cable de red dañado',
    'Puerto de red sin conexión',
    'Proyector no enciende',
    'Problemas de audio',
    'No se puede iniciar sesión',
    'Software no funciona correctamente',
    'Equipo no enciende'
];

$lugares = [
    'Sala 101',
    'Sala 102',
    'Sala 103',
    'Sala 104',
    'Sala 105',
    'Sala 162',
    'Laboratorio 1',
    'Laboratorio 2',
    'Oficina administrativa',
    'Biblioteca'
];

$estados = [
    'Pendiente',
    'En proceso',
    'Resuelto'
];

$id_usuario = 1;
$cantidad = 20;

try {
    $sql = 'INSERT INTO ticket
                (id_usuario, encargado, descripcion, lugar, estado)
            VALUES
                (:id_usuario, :encargado, :descripcion, :lugar, :estado)';

    $stmt = $pdo->prepare($sql);

    $pdo->beginTransaction();

    for ($i = 0; $i < $cantidad; $i++) {

        $stmt->execute([
            'id_usuario' => $id_usuario,
            'encargado' => $encargados[array_rand($encargados)],
            'descripcion' => $descripciones[array_rand($descripciones)],
            'lugar' => $lugares[array_rand($lugares)],
            'estado' => $estados[array_rand($estados)]
        ]);
    }

    $pdo->commit();

    echo "$cantidad tickets creados correctamente.";

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);

    echo 'No se pudieron crear los tickets.';
}