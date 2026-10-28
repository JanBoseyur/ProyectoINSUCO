
<?php

require_once __DIR__ . '/../models/Ticket.php';

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