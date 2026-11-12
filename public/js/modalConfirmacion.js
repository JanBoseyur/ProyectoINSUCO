
function crearModalEliminar() {

    let modal = document.getElementById('modalEliminarTicket');

    // Si ya existe, reutilizarlo
    if (modal) {
        return modal;
    }

    modal = document.createElement('div');

    modal.id = 'modalEliminarTicket';
    modal.className = 'modalOverlay';

    modal.innerHTML = `
        <div class="modalEliminar">

            <div class="modalEliminarIcon">
                <i data-lucide="alert-triangle"></i>
            </div>

            <h2>Eliminar ticket</h2>

            <p>
                ¿Estás seguro de que deseas eliminar este ticket?
                Esta acción también eliminará su historial y no se puede deshacer.
            </p>

            <div class="modalEliminarBotones">

                <button
                    type="button"
                    class="btnCancelarEliminar"
                    id="cancelarEliminar"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btnConfirmarEliminar"
                    id="confirmarEliminar"
                >
                    Eliminar ticket
                </button>

            </div>

        </div>
    `;

    document.body.appendChild(modal);

    if (window.lucide) {
        lucide.createIcons();
    }

    return modal;
}

async function eliminarTicket(idTicket) {

    const modal = crearModalEliminar();

    modal.classList.add('isOpen');

    const btnCancelar = document.getElementById('cancelarEliminar');
    const btnConfirmar = document.getElementById('confirmarEliminar');

    btnCancelar.onclick = () => {
        modal.classList.remove('isOpen');
    };

    btnConfirmar.onclick = async () => {

        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Eliminando...';

        try {

            const respuesta = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accion: 'eliminar_ticket',
                    id_ticket: idTicket
                })
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.message || 'No se pudo eliminar el ticket.');
            }

            modal.classList.remove('isOpen');

            window.location.reload();

        } catch (error) {

            alert(error.message);

            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Eliminar ticket';

        }

    };

    // Cerrar al presionar fuera
    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.classList.remove('isOpen');
        }
    };

}
