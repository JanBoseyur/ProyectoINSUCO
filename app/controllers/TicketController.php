
<?php

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/database.php';

function obtenerAdministradores(): array
{
    return listarAdministradores();
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

function fechaTicketEnEspanol(string $fecha): string
{
    $momento = new DateTimeImmutable($fecha);
    $dias = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
    ];
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];

    return sprintf(
        '%s %d de %s a las %s hrs',
        $dias[(int) $momento->format('N')],
        (int) $momento->format('j'),
        $meses[(int) $momento->format('n')],
        $momento->format('H:i')
    );
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
