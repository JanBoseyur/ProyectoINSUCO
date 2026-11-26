<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Registro | INSUCO</title>
    <link rel="stylesheet" href="/public/css/login.css">
    <link rel="stylesheet" href="/public/components/loginCard.css">
</head>
<body class="registerPage">
    <main class="loginCardContainer registerCard">
        <h1>Crear cuenta</h1>

        <?php if ($mensaje !== ''): ?>
            <p class="registerMessage" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="formContainer" action="/public/index.php?seccion=registro" method="POST">
            <label class="label" for="nombre">Nombre</label>
            <input class="inputField" type="text" id="nombre" name="nombre" maxlength="100" autocomplete="name" required>

            <label class="label" for="correo">Correo electrónico</label>
            <input class="inputField" type="email" id="correo" name="correo" maxlength="150" autocomplete="email" required>

            <label class="label" for="contrasena">Contraseña</label>
            <input class="inputField" type="password" id="contrasena" name="contrasena" minlength="8" autocomplete="new-password" required>

            <label class="label" for="rol">Escoge el rol:</label>
            <select class="inputField" name="rol" id="rol">
                <option value="admin">Admin</option>
                <option value="funcionario">Funcionario</option>
            </select>

            <button class="loginButton" type="submit">Registrarme</button>
        </form>

        <a class="passwordChangeLink" href="/public/index.php?seccion=login">Volver al inicio de sesión</a>
    </main>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/public/js/icons.js" defer></script>
</body>
</html>
