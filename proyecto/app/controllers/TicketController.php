
<?php

require_once __DIR__ . '/../models/Ticket.php';

function obtenerTickets(): array
{
    return listarTickets();
}