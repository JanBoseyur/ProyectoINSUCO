
<?php

require_once __DIR__ . '/../models/Ticket.php';

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
    string $lugar,
    string $descripcion
): int {
    return insertarTicket($idUsuario, $lugar, $descripcion);
}

# Funciones de Usuario

