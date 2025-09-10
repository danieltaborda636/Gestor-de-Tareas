<?php
// Pantalla de login
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Iniciar sesión</h2>
    <form method="POST" action="../../controllers/AuthController.php?action=login">
        <input type="email" name="email" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>
    <a href="register.php">Registrarse</a>
</body>
</html>
