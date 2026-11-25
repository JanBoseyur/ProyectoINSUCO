
<!DOCTYPE html>
<html lang = "es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel = "stylesheet" href = "/public/components/loginCard.css">
</head>

<div class = "loginCardContainer">
    
    <a href = "/app/views/auth/register.php" class = "pageHeader"">
        <i data-lucide = "circle-user"></i>
    </a>

    <i data-lucide = "wrench"></i>

    <h2>Iniciar Sesión</h2>

    <?php if (($_GET['contrasena'] ?? '') === 'actualizada'): ?>
        <p role="status">Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <form class = "formContainer" action = "/app/views/auth/login.php" method = "POST">
        
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
            <label class = "label for = "contrasena">Contraseña</label>
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

    <a class="passwordChangeLink" href="/app/views/auth/cambiarContrasena.php">Cambiar contraseña</a>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

</div>

