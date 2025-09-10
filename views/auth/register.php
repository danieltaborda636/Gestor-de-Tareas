<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de tareas</title>
</head>
<body>
    <?php
    include "./config/database.php";
    $conexion = connection();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['clave'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(name, email, password_hash) VALUES('$name', '$email', '$password')"; //Ingresamos los datos del usuario a la base de datos

        $sql2 = "SELECT * FROM users WHERE email = '$email'"; //hacemos una comparación del correo ingresado con los correos ya registrados en la base de datos
        $resultado2 = $conexion->query($sql2); //ejecutamos la consulta anterior


        if($resultado2->num_rows == 1){ //verificamos si hubo algun resultado, si es asi mandamos un echo diciendo que el correo esta registrado
          echo "<p style='color: red;'>El correo ya esta registrado.</p>";
        }else if($conexion->query($sql) === TRUE){ //si la consulta para mandar los datos a la base de datos se hace, le decimos al usuario que no hubo problema
          echo "<script>alert('Usuario registrado correctamente');</script>";
          header("Location: login.php"); //redirigimos al login para que inicie sesion
          exit;
        }else{
          echo "Error: ". $conexion->error; //mostramos el error si tuvimos algun problema
        }
    }
    ?>
    <main class="register">
      <form method="post">
        <input type="text" name="name" placeholder="Nombre" />
        <input type="text" name="email" placeholder="Correo Electrónico" />
        <input type="password" name="clave" placeholder="Contraseña" />
        <button>Registrar</button>
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a></p>
      </form>
    </main>
</body>
</html>