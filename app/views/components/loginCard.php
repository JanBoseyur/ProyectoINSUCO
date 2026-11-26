<?php $mensaje = is_string($mensaje ?? null) ? $mensaje : ''; ?>

<div class = "loginCardContainer">
    
    <a href = "/public/index.php?seccion=registro" class = "pageHeader">
        <i data-lucide = "circle-user"></i>
    </a>

    <i data-lucide = "wrench"></i>

    <h2>Iniciar Sesión</h2>

    <?php if ($mensaje !== ''): ?>
        <p role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class = "formContainer" action = "/public/index.php?seccion=login" method = "POST">
        
        <div class = "containerLabel">
            <i class = "iconLabel" data-lucide = "mail"></i>
            <label class = "label "for = "correo">Correo electrónico</label>
        </div>

        <input
            class = "inputField"
            type="email"
            id="correo"
            name="correo"
            placeholder="Ingresa tu correo"
            autocomplete="username"
            required
        >

        <div class = "containerLabel">
            <i class = "iconLabel" data-lucide = "lock"></i>
            <label class = "label" for = "contrasena">Contraseña</label>
        </div>

        <input
            class = "inputField"
            type="password"
            id="contrasena"
            name="contrasena"
            placeholder="Ingresa tu contraseña"
            autocomplete="current-password"
            required
        >

        <button class = "loginButton" type = "submit">
            Iniciar Sesión
        </button>

    </form>

    <a class="passwordChangeLink" href="/public/index.php?seccion=cambiar-contrasena">Cambiar contraseña</a>

</div>

