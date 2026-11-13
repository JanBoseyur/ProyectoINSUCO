
<?php

function registrarError(
    string $contexto,
    Throwable $error,
    array $datos = []
): void {

    $fecha = date('Y-m-d H:i:s');

    $mensaje = [
        "==================================================",
        "[{$fecha}] ERROR: {$contexto}",
        "Tipo: " . get_class($error),
        "Mensaje: " . $error->getMessage(),
        "Archivo: " . $error->getFile(),
        "Línea: " . $error->getLine(),
        "Código: " . $error->getCode(),
    ];

    if ($error instanceof PDOException && $error->errorInfo) {
        $mensaje[] = "SQLSTATE: " . ($error->errorInfo[0] ?? 'No disponible');
        $mensaje[] = "Código del controlador: " . ($error->errorInfo[1] ?? 'No disponible');
        $mensaje[] = "Detalle SQL: " . ($error->errorInfo[2] ?? 'No disponible');
    }

    if (!empty($datos)) {
        $mensaje[] = "Contexto adicional: " . print_r($datos, true);
    }

    $mensaje[] = "Pila de ejecución:";
    $mensaje[] = $error->getTraceAsString();
    $mensaje[] = "==================================================";

    error_log(implode(PHP_EOL, $mensaje) . PHP_EOL);
}
