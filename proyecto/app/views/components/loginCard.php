
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<div class = "loginCardContainer">

    <h2>Iniciar Sesión</h2>

    <form class = "formContainer" action = "/app/views/auth/login.php" method = "POST">

        <label for = "correo">Correo electrónico</label>
        
        <input
            type="email"
            id="correo"
            name="correo"
            placeholder="Ingresa tu correo"
            autocomplete="username"
            required
        >

        <label for = "contrasena">Contraseña</label>
        
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            placeholder="Ingresa tu contraseña"
            autocomplete="current-password"
            required
        >

        <button type = "submit" class = "loginButton">
            Iniciar Sesión
        </button>

    </form>

</div>

