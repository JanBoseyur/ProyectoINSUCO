
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel = "stylesheet" href = "/public/components/loginCard.css">
</head>

<div class = "loginCardContainer">
    
    <header class = "pageHeader">
        <h4>Plataforma de Soporte Técnico</h4>
    </header>

    <i data-lucide = "wrench"></i>

    <h2>Iniciar Sesión</h2>

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

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

</div>

