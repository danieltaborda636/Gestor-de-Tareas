<?php 
session_start();

// Inicializar variables de sesión si no existen
if (!isset($_SESSION["errorcorreo"])) {
    $_SESSION["errorcorreo"] = "";
}
if (!isset($_SESSION["Mensajerecuperacion"])) {
    $_SESSION["Mensajerecuperacion"] = "";
}
echo "Hora actual: " . date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidaste tu contraseña</title>
    <style>
        #alertacorreo, #alertamsg {
            font-size: 0.9rem;
            font-family: sans-serif;
            padding: 5px;
            margin-top: 5px;
        }
        #alertacorreo { color: red; }
        #alertamsg { color: blue; }
    </style>
</head>
<body>
    <h2>Recuperar contraseña</h2>
    <form action="recuperacion_de_contraseña.php" method="post">
        <label for="email">Ingresa el correo electrónico de tu cuenta:</label><br>
        <input type="email" name="email" id="email" required><br><br>
        <button type="submit">Enviar correo de recuperación</button>
    </form>

    <!-- Mensaje de error -->
    <?php if (!empty($_SESSION["errorcorreo"])): ?>
        <div id="alertacorreo"><?php echo $_SESSION["errorcorreo"]; ?></div>
        <script>
            setTimeout(() => {
                const alertacorreo = document.getElementById("alertacorreo");
                if (alertacorreo) alertacorreo.style.display = "none";
            }, 4000);
        </script>
        <?php unset($_SESSION["errorcorreo"]); ?>
    <?php endif; ?>

    <!-- Mensaje de éxito -->
    <?php if (!empty($_SESSION["Mensajerecuperacion"])): ?>
        <div id="alertamsg"><?php echo $_SESSION["Mensajerecuperacion"]; ?></div>
        <script>
            setTimeout(() => {
                const alertamsg = document.getElementById("alertamsg");
                if (alertamsg) alertamsg.style.display = "none";
            }, 4000);
        </script>
        <?php unset($_SESSION["Mensajerecuperacion"]); ?>
    <?php endif; ?>
    
</body>
</html>
