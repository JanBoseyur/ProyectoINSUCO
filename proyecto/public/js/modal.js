
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
    titulo.textContent = `Historial de Cambios Ticket #${idTicket}`;
    contenido.appendChild(titulo);

    const carga = document.createElement('p');
    carga.className = 'historialMensaje';
    carga.textContent = 'Cargando historial...';
    contenido.appendChild(carga);

    try {
        const respuesta = await fetch(
            `/public/api/historial_ticket.php?id_ticket=${encodeURIComponent(idTicket)}`,
            {
                headers: {
                    Accept: 'application/json'
                },
                credentials: 'same-origin'
            }
        );

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                datos.error || 'No se pudo cargar el historial.'
            );
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
            const tarjeta = document.createElement('article');
            tarjeta.className = 'historialRegistro';

            const encabezado = document.createElement('div');
            encabezado.className = 'historialEncabezado';

            const accion = document.createElement('h4');
            accion.className = 'historialAccion';
            accion.textContent = registro.tipo_cambio || 'Actividad';

            const fecha = document.createElement('span');
            fecha.className = 'historialFecha';
            fecha.textContent = registro.fecha_cambio || 'Fecha no disponible';

            encabezado.append(accion, fecha);
            tarjeta.appendChild(encabezado);

            const autor = document.createElement('p');
            autor.className = 'historialAutor';
            autor.textContent = `Generado por: ${registro.autor || 'No disponible'}`;
            tarjeta.appendChild(autor);

            if (registro.encargado && registro.encargado.trim() !== '') {
                const encargado = document.createElement('p');
                encargado.className = 'historialEncargado';
                encargado.textContent = `Encargado: ${registro.encargado}`;
                tarjeta.appendChild(encargado);
            }

            if (registro.estado_anterior || registro.estado_nuevo) {
                const estados = document.createElement('p');
                estados.className = 'historialEstados';

                if (registro.estado_anterior) {
                    const anterior = document.createElement('span');
                    anterior.className = `historialEstado ${obtenerClaseEstado(registro.estado_anterior)}`;
                    anterior.textContent = registro.estado_anterior;

                    estados.appendChild(anterior);
                }

                const flecha = document.createElement('span');
                flecha.className = 'historialFlecha';
                flecha.textContent = '→';
                estados.appendChild(flecha);

                if (registro.estado_nuevo) {
                    const nuevo = document.createElement('span');
                    nuevo.className = `historialEstado ${obtenerClaseEstado(registro.estado_nuevo)}`;
                    nuevo.textContent = registro.estado_nuevo;

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
