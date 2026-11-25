document.addEventListener('DOMContentLoaded', () => {
    const botonesFiltro = document.querySelectorAll('.buttonFilterSelector');
    const filas = Array.from(document.querySelectorAll('#tablaTickets tr[data-estado]'))
        .filter(fila => !fila.classList.contains('ticketDateRow'));
    const registros = filas.map(fila => ({
        fila,
        fecha: fila.nextElementSibling?.classList.contains('ticketDateRow')
            ? fila.nextElementSibling
            : null
    }));
    const paginacion = document.getElementById('paginacionTickets');
    const botonAnterior = document.getElementById('paginaAnterior');
    const botonSiguiente = document.getElementById('paginaSiguiente');
    const informacionPaginacion = document.getElementById('informacionPaginacion');
    const criterioBusqueda = document.getElementById('criterioBusquedaTicket');
    const campoBusqueda = document.getElementById('busquedaTicket');
    const filaSinResultados = document.getElementById('sinResultadosFiltro');
    const tamanoPagina = paginacion ? 5 : registros.length || 1;

    let filtroActual = document.querySelector('.buttonFilterSelector.active')?.dataset.filter || 'todos';
    let paginaActual = 1;

    function renderizarFilas() {
        const textoBusqueda = campoBusqueda?.value.trim().toLocaleLowerCase('es-CL') || '';
        const campoActual = criterioBusqueda?.value || 'funcionario';
        const registrosFiltrados = registros.filter(({ fila }) =>
            (filtroActual === 'todos' || fila.dataset.estado === filtroActual) &&
            (!textoBusqueda || (fila.dataset[campoActual] || '').toLocaleLowerCase('es-CL').includes(textoBusqueda))
        );
        const totalPaginas = Math.max(1, Math.ceil(registrosFiltrados.length / tamanoPagina));

        paginaActual = Math.min(paginaActual, totalPaginas);
        const inicio = (paginaActual - 1) * tamanoPagina;
        const fin = inicio + tamanoPagina;

        registros.forEach(({ fila, fecha }) => {
            fila.style.display = 'none';
            if (fecha) fecha.style.display = 'none';
        });
        registrosFiltrados.slice(inicio, fin).forEach(({ fila, fecha }) => {
            fila.style.display = '';
            if (fecha) fecha.style.display = '';
        });

        if (filaSinResultados) {
            filaSinResultados.hidden = registros.length === 0 || registrosFiltrados.length > 0;
        }

        if (paginacion) {
            paginacion.hidden = registrosFiltrados.length <= tamanoPagina;
            botonAnterior.disabled = paginaActual === 1;
            botonSiguiente.disabled = paginaActual === totalPaginas;
            informacionPaginacion.textContent = registrosFiltrados.length === 0
                ? 'No hay tickets para mostrar'
                : `Página ${paginaActual} de ${totalPaginas} · Mostrando ${inicio + 1}–${Math.min(fin, registrosFiltrados.length)} de ${registrosFiltrados.length}`;
        }
    }

    botonesFiltro.forEach(boton => {
        boton.addEventListener('click', () => {
            filtroActual = boton.dataset.filter;
            paginaActual = 1;

            botonesFiltro.forEach(otroBoton => {
                const activo = otroBoton === boton;
                otroBoton.classList.toggle('active', activo);
                otroBoton.setAttribute('aria-pressed', String(activo));
            });

            renderizarFilas();
        });
    });

    campoBusqueda?.addEventListener('input', () => {
        paginaActual = 1;
        renderizarFilas();
    });

    criterioBusqueda?.addEventListener('change', () => {
        paginaActual = 1;
        renderizarFilas();
        campoBusqueda?.focus();
    });

    botonAnterior?.addEventListener('click', () => {
        if (paginaActual > 1) {
            paginaActual--;
            renderizarFilas();
        }
    });

    botonSiguiente?.addEventListener('click', () => {
        paginaActual++;
        renderizarFilas();
    });

    renderizarFilas();
});
