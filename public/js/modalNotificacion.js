
document.addEventListener('DOMContentLoaded', () => {

    const modalTicketExito = document.getElementById('modalTicketExito');
    const btnCerrarModalExito = document.getElementById('btnCerrarModalExito');

    const tituloModalExito = document.getElementById('tituloModalExito');
    const mensajeModalExito = document.getElementById('mensajeModalExito');

    // Mostrar modal
    window.mostrarModalExito = function(titulo, mensaje) {

        if (!modalTicketExito) return;

        tituloModalExito.textContent = titulo;
        mensajeModalExito.textContent = mensaje;

        modalTicketExito.classList.add('isOpen');

        if (window.lucide) {
            lucide.createIcons();
        }
    };

    // Cerrar modal
    function cerrarModalExito() {
        modalTicketExito.classList.remove('isOpen');
    }

    // Evento del botón
    if (btnCerrarModalExito) {
        btnCerrarModalExito.addEventListener('click', cerrarModalExito);
    }

});