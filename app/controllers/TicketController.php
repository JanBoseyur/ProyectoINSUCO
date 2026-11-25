
<?php

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../../config/database.php';

function obtenerAdministradores(): array
{
    global $pdo;

    $sql = "SELECT id, nombre, correo
            FROM usuario
            WHERE rol = 'admin'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cambiarEstado(
    int $idTicket,
    string $estadoNuevo,
    int $idAutor,
    string $descripcionCambio = '',
    ?string $nuevoEncargado = null

): bool {
    
    return actualizarEstadoTicket(
        $idTicket,
        $estadoNuevo,
        $idAutor,
        $descripcionCambio,
        $nuevoEncargado
    );
}

function registrarTicket(
    int $idUsuario,
    int $idLugar,
    string $descripcion,
    string $categoria
): int {
    return insertarTicket(
        $idUsuario,
        $idLugar,
        $descripcion,
        $categoria
    );
}

function editarTicketFuncionario(
    int $idTicket,
    int $idUsuario,
    int $idLugar,
    string $categoria,
    string $descripcion
): bool {
    return actualizarTicketFuncionario(
        $idTicket,
        $idUsuario,
        $idLugar,
        $categoria,
        $descripcion
    );
}

# Funciones Administrativas
function obtenerTickets(): array
{
    return listarTickets();
}

function obtenerTicketsAbiertos(): int
{
    return listarTicketsAbiertos();
}

function obtenerTicketsEnProceso(): int
{
    return listarTicketsEnProceso();
}

function obtenerTicketsResueltos(): int
{
    return listarTicketsResueltos();
}

function obtenerTicketsFuncionario(int $idUsuario): array
{
    return listarTicketsPorUsuario($idUsuario);
}

function obtenerLugaresActivos(): array
{
    return listarLugaresActivos();
}

function agregarDependenciaActiva(string $nombre): int
{
    return agregarDependencia($nombre);
}

function desactivarDependencia(int $idDependencia): bool
{
    return eliminarDependencia($idDependencia);
}

function obtenerCategoriasActivas(): array
{
    return listarCategorias();
}

function desactivarCategoria(int $idCategoria): bool
{
    return eliminarCategoria($idCategoria);
}
