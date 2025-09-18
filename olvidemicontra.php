<?php 
session_start();
if(!isset($_SESSION["errordecorreo"])){
    $_SESSION["errordecorreo"]="";
    $_SESSION["Mensajerecuperacion"]="";

}
if(!isset($_SESSION["Mensajerecuperacion"])){
    
    $_SESSION["Mensajerecuperacion"]="";

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidaste tu contraseña</title>
</head>
<body>
    <form action="recuperacion_de_Contraseña.php" method="post">
        <label for="">Ingresa el correo electrónico de tu cuenta</label>
        <input type="email" name="email" required>
        <button class="boton">Enviar correo de recuperación</button>
        <?php if(!empty($_SESSION["errorcorreo"])): ?>
                <div id="alertacorreo" style="color:red; font-size:0.5rem; font-family:sans-serif; width:150px;">
                    <?php 
                    echo $_SESSION["errorcorreo"];
                    ?>
                </div>
                <script>
                    setTimeout(() => {
                    const alertacorreo=document.getElementById("alertacorreo");
                    if(alertacorreo){
                        alertacorreo.style.display="none";
                    } 
                    }, 4000);
                </script>
                <?php 
                unset($_SESSION['errorcorreo']);
                endif;
                echo $_SESSION["Mensajerecuperacion"];
                ?>
    </form>
</body>
</html>