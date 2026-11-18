
document.addEventListener('DOMContentLoaded', () => {

    const botones = document.querySelectorAll('.buttonFilterSelector');
    const filas = document.querySelectorAll('#tablaTickets tr[data-estado]');

    // Función principal para filtrar tickets.
    function filtrarTickets(filtroSeleccionado) {

        filas.forEach(fila => {

            const estado = fila.dataset.estado;

            let mostrar = false;

            switch (filtroSeleccionado) {

                case 'todos':
                    mostrar = true;
                    break;

                case 'Pendiente':
                case 'En proceso':
                case 'Resuelto':
                    mostrar = estado === filtroSeleccionado;
                    break;
                            }

                            fila.style.display = mostrar ? '' : 'none';

                        });

                    }

    // Eventos de los botones.
    botones.forEach(boton => {

        boton.addEventListener('click', () => {

            const filtroSeleccionado = boton.dataset.filter;

            // Actualizar botones activos.
            botones.forEach(btn => {

                const activo = btn === boton;

                btn.classList.toggle('active', activo);
                btn.setAttribute('aria-pressed', String(activo));

            });

            // Aplicar filtro.
            filtrarTickets(filtroSeleccionado);

        });

    });

    // Aplicar filtro inicial: tickets abiertos.
    const botonInicial = document.querySelector(
        '.buttonFilterSelector[data-filter="abiertos"]'
    );

    if (botonInicial) {
        botonInicial.click();
    }

});
