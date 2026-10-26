
<?php
    $titulo = $titulo ?? 'Métrica';
    $subtitulo = $subtitulo ?? '';
    $icono = $icono ?? 'bi-bar-chart';
    $valor = $valor ?? 0;
?>

<head>
    <meta name = "viewport" content="width=device-width, initial-scale=1">
    <link rel = "stylesheet" href = "/public/components/metricCard.css">

    <link rel = "preconnect" href="https://fonts.googleapis.com">
    <link rel = "preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href = "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<div class = "metricCard">
    
    <div class = "metricHeader">
        <span><?= htmlspecialchars($titulo) ?></span>

        <i data-lucide="<?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?>"></i>
    </div>

    <h2><?= htmlspecialchars((string) $valor) ?></h2>

    <p><?= htmlspecialchars($subtitulo) ?></p>

    <script src = "https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

</div>