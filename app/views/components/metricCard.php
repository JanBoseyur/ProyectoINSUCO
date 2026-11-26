
<?php
    $titulo = $titulo ?? 'Métrica';
    $subtitulo = $subtitulo ?? '';
    $icono = $icono ?? 'bi-bar-chart';
    $valor = $valor ?? 0;
?>

<div class = "metricCard">
    
    <div class = "metricHeader">
        <span><?= htmlspecialchars($titulo) ?></span>

        <i data-lucide="<?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?>"></i>
    </div>

    <h2><?= htmlspecialchars((string) $valor) ?></h2>

    <p><?= htmlspecialchars($subtitulo) ?></p>

</div>
