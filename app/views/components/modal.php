
<?php

$title = $titulo ?? '';
$tituloModal = $contenidoModal ?? '';
$idModal = $idModal ?? 'modal';
$anchoModal = $anchoModal ?? '10000px';

?>

<div
    class = "modalOverlay"
    id = "<?= htmlspecialchars($idModal) ?>"
    aria-hidden = "true"
>
    <section
        class = "modalContainer"
        role = "dialog"
        aria-modal = "true"
        aria-labelledby = "<?= htmlspecialchars($idModal) ?>Titulo"
        style = "--modal-width: <?= htmlspecialchars($anchoModal) ?>"
    >
        <header class = "modalHeader1" id = "modalHeader1">
            
            <div class = "modalHistorialIcon">
                <i data-lucide = "rotate-ccw-clock"></i>
            </div>

            <h2 class = "tituloModal">
                Historial de Cambios
            </h2>

            <button
                type = "button"
                class = "modalClose"
                data-modal-close
                aria-label = "Cerrar modal"
            >
                &times;
            </button>

        </header>

        <div class = "modalHeader1-2" id = "modalHeader1-2">
            <?= $contenidoModal ?>
        </div>

    </section>

</div>
