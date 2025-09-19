<?php 
session_start();

// Validar que exista el token en la URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error'] = "Token no válido o no proporcionado.";
    header("Location: olvidemicontra.php");
    exit();
}

$token = $_GET['token'];
$_SESSION['token'] = $token; // Guardamos el token para usarlo en el formulario
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña</title>
</head>
<body>
    <?php if (!empty($_SESSION['resultado'])): ?>
        <div style="color: red;">
            <?php 
            echo $_SESSION['resultado'];  
            $_SESSION['resultado'] = ""; // Limpiamos el mensaje
            ?>
        </div>
    <?php endif; ?>

    <form action="controllers/validarclave.php" method="post">
        <label for="contraseña">Ingrese su nueva contraseña</label>
        <input type="password" name="contraseña" placeholder="Ingrese su contraseña" required>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <button class="boton">Cambiar contraseña</button>
    </form>
</body>
</html>
