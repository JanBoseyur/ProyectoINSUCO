
document.addEventListener('DOMContentLoaded', () => {
    let destinoCierre = '';

    const modal = document.createElement('div');
    modal.id = 'modalCerrarSesion';
    modal.className = 'modalOverlay';
    modal.setAttribute('aria-hidden', 'true');
    modal.innerHTML = `
        <section class="modalEliminar" role="dialog" aria-modal="true" aria-labelledby="tituloCerrarSesion">
            <div class="modalEliminarIcon">
                <i data-lucide="log-out"></i>
            </div>

            <h2 id="tituloCerrarSesion">Cerrar sesión</h2>
            <p>¿Estás seguro de que quieres cerrar sesión?</p>

            <div class="modalEliminarBotones">
                <button type="button" class="btnCancelarEliminar" data-cancelar-cierre>
                    Cancelar
                </button>
                <button type="button" class="btnConfirmarEliminar" data-confirmar-cierre>
                    Cerrar sesión
                </button>
            </div>
        </section>
    `;
    document.body.appendChild(modal);

    const botonCancelar = modal.querySelector('[data-cancelar-cierre]');
    const botonConfirmar = modal.querySelector('[data-confirmar-cierre]');

    function cerrarModal() {
        modal.classList.remove('isOpen');
        modal.setAttribute('aria-hidden', 'true');
        destinoCierre = '';
    }

    document.addEventListener('click', evento => {
        const enlace = evento.target.closest('[data-confirm-logout]');
        if (!enlace) return;

        evento.preventDefault();
        destinoCierre = enlace.href;
        modal.classList.add('isOpen');
        modal.setAttribute('aria-hidden', 'false');
        botonCancelar.focus();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });

    botonCancelar.addEventListener('click', cerrarModal);

    botonConfirmar.addEventListener('click', () => {
        if (destinoCierre) {
            window.location.assign(destinoCierre);
        }
    });

    modal.addEventListener('click', evento => {
        if (evento.target === modal) cerrarModal();
    });

    document.addEventListener('keydown', evento => {
        if (evento.key === 'Escape' && modal.classList.contains('isOpen')) {
            cerrarModal();
        }
    });
});
