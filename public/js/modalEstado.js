
let ticketSeleccionado = null;
let estadoActualSeleccionado = null;
let procesandoCambioEstado = false;

function abrirModalEstado(idTicket, estadoActual) {
    ticketSeleccionado = Number(idTicket);

    estadoActualSeleccionado = String(estadoActual)
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-');

    document.getElementById('descripcionCambio').value = '';
    document.getElementById('nuevoEncargado').value = '';
    
    abrirModal('modalEstado');

    document.querySelectorAll('#modalEstado [data-estado]')
        .forEach(boton => {
            boton.setAttribute(
                'aria-pressed',
                String(boton.dataset.estado === estadoActualSeleccionado)
            );
        });
}

document.querySelectorAll('#modalEstado [data-estado]')
    .forEach(boton => {
        boton.addEventListener('click', async () => {
            if (!ticketSeleccionado || procesandoCambioEstado) {
                return;
            }

            const descripcionCambio = document
                .getElementById('descripcionCambio')
                .value.trim();

            const nuevoEncargado = document
                .getElementById('nuevoEncargado')
                .value.trim();

            const estados = {
                'pendiente': 'Pendiente',
                'en-proceso': 'En proceso', 
                'resuelto': 'Resuelto'
            };

            const nuevoEstado = estados[boton.dataset.estado];

            if (!nuevoEstado) {
                alert('El estado seleccionado no es válido.');
                return;
            }

            if (boton.dataset.estado === estadoActualSeleccionado) {
                cerrarModal(document.getElementById('modalEstado'));
                return;
            }

            procesandoCambioEstado = true;

            try {
                const respuesta = await fetch('/public/api/accion_ticket.php',
                    {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        accion: 'cambiar_estado',
                        id_ticket: ticketSeleccionado,
                        estado: nuevoEstado,
                        descripcion: descripcionCambio,
                        encargado: nuevoEncargado
                    })
                });

                const respuestaTexto = await respuesta.text();
                let datos;

                try {
                    datos = JSON.parse(respuestaTexto);
                } catch (error) {
                    console.error('Respuesta no JSON al cambiar el estado:', respuestaTexto);
                    throw new Error(
                        'El servidor devolvió una respuesta inesperada. Recarga la página e inténtalo de nuevo.'
                    );
                }

                if (!respuesta.ok || !datos.success) {
                    throw new Error(
                        datos.message || 'No se pudo cambiar el estado.'
                    );
                }

                cerrarModal(document.getElementById('modalEstado'));

                window.location.reload();

            } catch (error) {
                console.error('Error al cambiar el estado:', error);
                alert(error.message || 'Error al cambiar el estado.');

            } finally {
                procesandoCambioEstado = false;
            }
        });
    });
