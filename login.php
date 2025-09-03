
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
   
    <div class="contenedorFormulario">
        <form method="post"> <!--es el formulario para el inicio de sesion del usuario-->
            <h3>Ingrese Correo </h3>
            <input type="email" name="correo" id="gmail" placeholder="Correo electronico">
            <h3>Contraseña</h3>
            <input type="password" name="contrasena" id="contraseña" placeholder="contraseña">
            <input type="submit" value="Enviar">
            <!-- <button name="btningresar" type="submit" id="buttonInicioSesion">iniciar Sesion</button> -->
             
            <p> <a id="regsitrate" href="register.php">Registrarme </a>
                <a id="olvidasteContraseña"  href="#">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
        <?php
            include "controller/UserController.php"
        ?>
    </div>
</body>
</html>