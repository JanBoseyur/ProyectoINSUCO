
function abrirModal(id) {
    const modal = document.getElementById(id);

    if (!modal) return;

    modal.classList.add('isOpen');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modalAbierto');

    modal.querySelector('[data-modal-close]')?.focus();
}

function cerrarModal(modal) {
    if (!modal) return;

    modal.classList.remove('isOpen');
    modal.setAttribute('aria-hidden', 'true');

    if (!document.querySelector('.modalOverlay.isOpen')) {
        document.body.classList.remove('modalAbierto');
    }
}

async function abrirHistorial(idTicket) {
    abrirModal('modalHistorial');

    const contenido = document.getElementById('contenidoHistorial');
    if (!contenido) return;

    contenido.replaceChildren();

    const titulo = document.createElement('h3');
    titulo.className = 'historialTitulo';
    titulo.textContent = `Ticket #${idTicket}`;

    const carga = document.createElement('p');
    carga.className = 'historialMensaje';
    carga.textContent = 'Cargando historial...';

    contenido.append(titulo, carga);

    try {
        const respuesta = await fetch(
            `/public/api/historial_ticket.php?id_ticket=${encodeURIComponent(idTicket)}`,
            {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            }
        );

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || 'No se pudo cargar el historial.');
        }

        if (!Array.isArray(datos)) {
            throw new Error('La respuesta del servidor no es válida.');
        }

        contenido.replaceChildren();
        contenido.appendChild(titulo);

        if (datos.length === 0) {
            const mensaje = document.createElement('p');
            mensaje.className = 'historialMensaje';
            mensaje.textContent =
                'Este ticket todavía no tiene cambios registrados.';

            contenido.appendChild(mensaje);
            return;
        }

        const lista = document.createElement('div');
        lista.className = 'historialLista';

        datos.forEach(registro => {
            const estadoAnterior = registro.estado_anterior ?? null;
            const estadoSiguiente = registro.estado_nuevo ?? null;
            const fecha = registro.fecha_cambio ?? 'Fecha no disponible';
            const autor = registro.autor ?? 'No disponible';

            const tarjeta = document.createElement('article');
            tarjeta.className = 'historialRegistro';

            const encabezado = document.createElement('div');
            encabezado.className = 'historialEncabezado';

            const accion = document.createElement('h4');
            accion.className = 'historialAccion';
            accion.textContent = registro.tipo_cambio || 'Actividad';

            const elementoFecha = document.createElement('time');
            elementoFecha.className = 'historialFecha';
            elementoFecha.textContent = fecha;

            if (registro.fecha_cambio) {
                elementoFecha.dateTime =
                    String(registro.fecha_cambio).replace(' ', 'T');
            }

            encabezado.append(accion, elementoFecha);
            tarjeta.appendChild(encabezado);

            const elementoAutor = document.createElement('p');
            elementoAutor.className = 'historialAutor';
            elementoAutor.textContent = `Generado por: ${autor}`;
            tarjeta.appendChild(elementoAutor);

            if (registro.encargado?.trim()) {
                const encargado = document.createElement('p');
                encargado.className = 'historialEncargado';
                encargado.textContent = `Encargado: ${registro.encargado}`;
                tarjeta.appendChild(encargado);
            }

            if (estadoAnterior || estadoSiguiente) {
                const estados = document.createElement('div');
                estados.className = 'historialEstados';

                if (estadoAnterior) {
                    const anterior = document.createElement('span');
                    anterior.className =
                        `historialEstado ${obtenerClaseEstado(estadoAnterior)}`;
                    anterior.textContent = estadoAnterior;
                    estados.appendChild(anterior);
                }

                if (estadoAnterior && estadoSiguiente) {
                    const flecha = document.createElement('span');
                    flecha.className = 'historialFlecha';
                    flecha.textContent = '→';
                    estados.appendChild(flecha);
                }

                if (estadoSiguiente) {
                    const nuevo = document.createElement('span');
                    nuevo.className =
                        `historialEstado ${obtenerClaseEstado(estadoSiguiente)}`;
                    nuevo.textContent = estadoSiguiente;
                    estados.appendChild(nuevo);
                }

                tarjeta.appendChild(estados);
            }

            if (registro.detalle) {
                const detalle = document.createElement('p');
                detalle.className = 'historialDetalle';
                detalle.textContent = registro.detalle;
                tarjeta.appendChild(detalle);
            }

            lista.appendChild(tarjeta);
        });

        contenido.appendChild(lista);

    } catch (error) {
        console.error('Error al cargar el historial:', error);

        contenido.replaceChildren();

        const mensaje = document.createElement('p');
        mensaje.className = 'historialError';
        mensaje.textContent =
            error.message || 'Error al cargar el historial.';

        contenido.append(titulo, mensaje);
    }
}

function obtenerClaseEstado(estado) {
    const estadoNormalizado = String(estado)
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '-');

    const clasesPermitidas = [
        'pendiente',
        'en-proceso',
        'resuelto',
        'resueltos',
        'cancelado'
    ];

    return clasesPermitidas.includes(estadoNormalizado)
        ? `estado-${estadoNormalizado}`
        : 'estado-desconocido';
}

document.addEventListener('click', evento => {
    const botonCerrar = evento.target.closest('[data-modal-close]');

    if (botonCerrar) {
        const modal = botonCerrar.closest('.modalOverlay');

        if (modal) cerrarModal(modal);
        return;
    }

    if (evento.target.classList.contains('modalOverlay')) {
        cerrarModal(evento.target);
    }
});

document.addEventListener('keydown', evento => {
    if (evento.key === 'Escape') {
        document.querySelectorAll('.modalOverlay.isOpen')
            .forEach(cerrarModal);
    }
});