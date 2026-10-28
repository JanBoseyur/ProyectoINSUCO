
<head>
    <link rel = "stylesheet" href="/public/components/modalState.css">
</head> 

<div class = "modalOverlay" id = "modalEstado" aria-hidden="true">
    <section
        class = "modalContainer modalEstadoContainer"
        role = "dialog"
        aria-modal="true"
        aria-labelledby="tituloModalEstado"
    >
        <header class = "modalHeader">
            <h2 id = "tituloModalEstado">Cambiar estado</h2>

            <button
                type="button"
                class="modalClose"
                data-modal-close
                aria-label="Cerrar modal"
            >
                &times;
            </button>
        </header>

        <div class = "modalBody">
            <p class = "modalEstadoDescripcion">
                Selecciona el nuevo estado del ticket.
            </p>

            <div class = "campoCambio">
                <label for = "descripcionCambio">Descripción del cambio (opcional)</label>
                <textarea
                    id = "descripcionCambio"
                    rows = "3"
                    maxlength = "1000"
                    placeholder = "Explica brevemente el motivo del cambio..."
                ></textarea>
            </div>

            <div class = "campoCambio">
                <label for = "nuevoEncargado">Nuevo encargado (opcional)</label>
                <input
                    type="text"
                    id="nuevoEncargado"
                    maxlength="150"
                    placeholder="Deja vacío para mantener el encargado actual"
                >
                <small>Si lo dejas vacío, no se modificará el encargado actual.</small>
            </div>

            <div class = "opcionesEstado">
                
                <button type = "button" class = "opcionEstado pendiente"
                    data-estado = "pendiente">
                    <i data-lucide = "clock"></i>
                    <span>Pendiente</span>
                </button>

                <button type = "button" class = "opcionEstado en-proceso"
                    data-estado = "en-proceso">
                    <i data-lucide="wrench"></i>
                    <span>En proceso</span>
                </button>

                <button type = "button" class = "opcionEstado resuelto"
                    data-estado = "resuelto">
                    <i data-lucide="circle-check"></i>
                    <span>Resuelto</span>
                </button>

            </div>

        </div>

    </section>
</div>
