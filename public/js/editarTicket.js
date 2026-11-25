document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalEditarTicket');
    const formulario = document.getElementById('formEditarTicket');
    if (!modal || !formulario) return;

    const campoId = document.getElementById('editarIdTicket');
    const campoCategoria = document.getElementById('editarCategoria');
    const campoLugar = document.getElementById('editarLugar');
    const campoDescripcion = document.getElementById('editarDescripcion');
    const mensaje = document.getElementById('mensajeEditarTicket');
    const token = formulario.elements.csrf_token.value;

    const cerrar = () => {
        modal.classList.remove('isOpen');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modalAbierto');
    };

    document.querySelectorAll('[data-editar-ticket]').forEach(boton => {
        boton.addEventListener('click', () => {
            campoId.value = boton.dataset.id;
            campoCategoria.value = boton.dataset.categoria;
            campoLugar.value = boton.dataset.idLugar;
            campoDescripcion.value = boton.dataset.descripcion;
            mensaje.textContent = '';
            mensaje.classList.remove('isError');
            modal.classList.add('isOpen');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modalAbierto');
            campoCategoria.focus();
        });
    });

    modal.querySelectorAll('[data-modal-close]').forEach(boton => {
        boton.addEventListener('click', cerrar);
    });

    modal.addEventListener('click', evento => {
        if (evento.target === modal) cerrar();
    });

    document.addEventListener('keydown', evento => {
        if (evento.key === 'Escape' && modal.classList.contains('isOpen')) cerrar();
    });

    formulario.addEventListener('submit', async evento => {
        evento.preventDefault();
        mensaje.textContent = '';
        mensaje.classList.remove('isError');

        const botonGuardar = formulario.querySelector('[type="submit"]');
        botonGuardar.disabled = true;

        try {
            const respuesta = await fetch('/public/api/editar_ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    csrf_token: token,
                    id_ticket: campoId.value,
                    id_lugar: campoLugar.value,
                    categoria: campoCategoria.value,
                    descripcion: campoDescripcion.value
                })
            });
            const datos = await respuesta.json();
            if (!respuesta.ok || !datos.success) {
                throw new Error(datos.message || 'No se pudo actualizar el ticket.');
            }

            const botonFila = document.querySelector(`[data-editar-ticket][data-id="${CSS.escape(campoId.value)}"]`);
            const fila = botonFila?.closest('tr');
            if (fila) {
                fila.cells[0].textContent = campoCategoria.value;
                fila.cells[1].textContent = campoLugar.selectedOptions[0].textContent;
                fila.cells[2].textContent = campoDescripcion.value;
                botonFila.dataset.categoria = campoCategoria.value;
                botonFila.dataset.idLugar = campoLugar.value;
                botonFila.dataset.descripcion = campoDescripcion.value;
            }
            cerrar();
        } catch (error) {
            mensaje.textContent = error.message;
            mensaje.classList.add('isError');
        } finally {
            botonGuardar.disabled = false;
        }
    });
});
