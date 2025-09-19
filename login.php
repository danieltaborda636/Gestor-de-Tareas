<?php
// index.php (vista)

// Iniciamos la sesión para poder leer mensajes flash y datos del usuario si está logueado.
session_start();



// Comprobamos si el usuario está logueado (existencia de $_SESSION['user']).
$loggedUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="./css/style.css">
  <title>FORMULARIO DE REGISTRO E INICIO SESIÓN</title>
</head>
<body>



<?php if ($loggedUser): // Si el usuario está logueado mostramos su nombre ?>
  
<?php endif; ?>

<!-- Aquí va tu HTML del formulario (lo ajusté para que mande al controlador) -->
    <!--registro-->
    <div class="container-form register">
        <div class="information">
            <div class="info-childs">
                <h2>Bienvenido</h2>
                <p>Para unirte a nuestra comunidad por favor Inicia Sesión con tus datos</p>
                <input type="button" value="Iniciar Sesión" id="sign-in">
            </div>
        </div>
        <div class="form-information">
            <div class="form-information-childs">
                <h2>Crear una Cuenta</h2>
                <div class="icons">
                    <i class='bx bxl-google'></i>
                    <i class='bx bxl-github'></i>
                    <i class='bx bxl-linkedin' ></i>
                </div>
                <p>o usa tu email para registrarte</p>
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                 }

                // Mostrar mensaje flash si existe
                if (isset($_SESSION['flash'])) {
                    $flash = $_SESSION['flash'];
                    $type = $flash['type']; // 'success' o 'error'
                    $message = $flash['message'];

                    // Puedes personalizar el HTML según el tipo de mensaje
                    if ($type === 'success') {
                        echo "<div style='padding:10px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:5px; margin-bottom:10px;'>$message</div>";
                    } elseif ($type === 'error') {
                        echo "<div style='padding:10px; background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:5px; margin-bottom:10px;'>$message</div>";
                    }

                    // Limpiar mensaje para que no se muestre nuevamente
                    unset($_SESSION['flash']);
                }
                ?>
                
                <!-- FORMULARIO DE REGISTRO: action apunta al controlador con action=register -->
                <form class="form form-register" novalidate method="POST" action="controllers/AuthController.php?action=register">
                    <div>
                        <label>
                            <i class='bx bx-user' ></i>
                            <!-- nombre de usuario -->
                            <input type="text" placeholder="Nombre Usuario" name="userName" >
                        </label>
                    </div>
                    <div>
                        <label >
                            <i class='bx bx-envelope' ></i>
                            <!-- correo elctronico -->
                            <input type="email" placeholder="Correo Electronico" name="userEmail" >
                        </label>
                    </div>
                   <div>
                        <label>
                            <i class='bx bx-lock-alt' ></i>
                            <!-- password -->
                            <input type="password" placeholder="Contraseña" name="userPassword">
                        </label>
                   </div>

                    <input type="submit" value="Registrarse">
                </form>
            </div>
        </div>
    </div>

<!-- inicio de sesion -->
    <div class="container-form login hide">
        <div class="information">
            <div class="info-childs">
                <h2>¡¡Bienvenido nuevamente!!</h2>
                <p>Para unirte a nuestra comunidad por favor Inicia Sesión con tus datos</p>
                <input type="button" value="Registrarse" id="sign-up">
            </div>
        </div>
        <div class="form-information">
            <div class="form-information-childs">
                <h2>Iniciar Sesión</h2>
                <div class="icons">
                    <i class='bx bxl-google'></i>
                    <i class='bx bxl-github'></i>
                    <i class='bx bxl-linkedin' ></i>
                </div>
                <p>o Iniciar Sesión con una cuenta</p>
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                 }

                // Mostrar mensaje flash si existe
                if (isset($_SESSION['flash'])) {
                    $flash = $_SESSION['flash'];
                    $type = $flash['type']; // 'success' o 'error'
                    $message = $flash['message'];

                    // Puedes personalizar el HTML según el tipo de mensaje
                    if ($type === 'success') {
                        echo "<div style='padding:10px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:5px; margin-bottom:10px;'>$message</div>";
                    } elseif ($type === 'error') {
                        echo "<div style='padding:10px; background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:5px; margin-bottom:10px;'>$message</div>";
                    }

                    // Limpiar mensaje para que no se muestre nuevamente
                    unset($_SESSION['flash']);
                }
                ?>
                
                <!-- FORMULARIO DE LOGIN: action apunta al controlador con action=login -->
                <form class="form form-login" novalidate method="POST" action="controllers/AuthController.php?action=login">
                    <div>
                        <label >
                            <i class='bx bx-envelope' ></i>
                            <!-- correo electronico -->
                            <input type="email" placeholder="Correo Electronico" name="userEmail">
                        </label>
                    </div>
                    <div>
                        <label>
                            <i class='bx bx-lock-alt' ></i>
                            <!-- password -->
                            <input type="password" placeholder="Contraseña" name="userPassword">
                        </label>
                    </div>
                    <input type="submit" value="Iniciar Sesión">
                </form>
                <a href="olvidemicontra.php">¿olvidaste tu clave?</a>
            </div>
        </div>
    </div>

    <script src="./js/script.js"></script>
</body>
</html>
