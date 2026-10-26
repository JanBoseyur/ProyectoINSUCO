
<?php

$title = $titulo ?? '';
$tituloModal = $contenidoModal ?? '';
$idModal = $idModal ?? 'modal';
$anchoModal = $anchoModal ?? '600px';

?>

<link rel = "stylesheet" href = "/public/components/modal.css">

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
        <header class = "modalHeader">

            <button
                type = "button"
                class = "modalClose"
                data-modal-close
                aria-label = "Cerrar modal"
            >
                &times;
            </button>

        </header>

        <div class = "modalHeader">
            <h2 id = "<?= htmlspecialchars($idModal)?>Titulo">
                <?= $contenidoModal ?>
            </h2>
        </div>
    
        <!--
        <div class="modalBody">
        </div>
        -->
        
    </section>
</div>
