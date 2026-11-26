
<div
    class="modalOverlay"
    id="modalTicketExito"
    data-auto-open="<?= ($tipoMensaje ?? '') === 'exito' ? 'true' : 'false' ?>"
    data-title="¡Ticket creado con éxito!"
    data-message="El ticket ha sido registrado correctamente en el sistema."
>

    <div class = "modalContainer modalExito">

        <div class = "modalExitoIcon">
            <i data-lucide = "check"></i>
        </div>

        <h2 id = "tituloModalExito"></h2>

        <p id = "mensajeModalExito"></p>

        <button type = "button" id = "btnCerrarModalExito">
            Aceptar
        </button>

    </div>

</div>
