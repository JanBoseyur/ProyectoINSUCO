<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña | INSUCO</title>
    <link rel="stylesheet" href="/public/css/login.css">
    <link rel="stylesheet" href="/public/components/loginCard.css">
    <style>
        body { width: 100%; color: #222; }
        .passwordCard { width: min(100% - 32px, 420px); padding: 32px; height: auto; margin: 8vh auto; border-radius: 12px; }
        .passwordCard h1 { margin: 0 0 8px; font-size: 1.5rem; }
        .passwordCard p { margin: 0 0 20px; color: #555; text-align: center; }
        .passwordCard .formContainer { width: 100%; }
        .passwordCard .loginButton { width: 100%; }
        .passwordCard .passwordChangeLink { color: #222; }
        .formError { color: #a12626; text-align: center; }
    </style>
</head>
<body>
    <main class="loginCardContainer passwordCard">
        <h1>Cambiar contraseña</h1>
        <p>Ingresa tu correo y la nueva contraseña.</p>

        <?php if ($error !== null): ?>
            <p class="formError" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="formContainer" action="/public/index.php?seccion=cambiar-contrasena" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <label class="label" for="correo">Correo electrónico</label>
            <input class="inputField" type="email" id="correo" name="correo" autocomplete="username" required>

            <label class="label" for="contrasena_nueva">Nueva contraseña</label>
            <input class="inputField" type="password" id="contrasena_nueva" name="contrasena_nueva" minlength="8" maxlength="4096" autocomplete="new-password" required>

            <button class="loginButton" type="submit">Actualizar contraseña</button>
        </form>
        <a class="passwordChangeLink" href="/public/index.php?seccion=login">Volver al inicio de sesión</a>
    </main>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/public/js/icons.js" defer></script>
</body>
</html>
