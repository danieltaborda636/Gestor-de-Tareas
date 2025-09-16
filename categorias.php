<?php
session_start();


require_once __DIR__ . "/config/database.php";

$conn = Database::connect();

$sentencia = $conn->prepare("SELECT * FROM categorias");
$sentencia->execute();
$result = $sentencia->get_result();

$categorias = $result->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="./views/categorias/crearCategoria.php" method="POST">
        <label for="nombre">Nombre</label>
        <input type="text" placeholder="nombre de la categoria" name="nombre">
        <button type="submit">Crear</button>
    </form>

    <?php if (isset($_SESSION["mensaje"])): ?>
        <h1 style="color:green;">
            <?= $_SESSION["mensaje"] ?>
        </h1>
    <?php endif ?>


    <?php if (isset($_SESSION["error"])): ?>
        <h1 style="color:red;">
            <?= $_SESSION["error"] ?>
        </h1>
    <?php endif ?>


    <ul>
        <?php if (!empty($categorias)): ?>
            <?php  foreach($categorias as $categoria): ?>
                    <li>
                        <?= $categoria["nombre"] ?>
                        <form action="./views/categorias/eliminarCategoria.php" method = "POST">
                            <input type="hidden" name="id" value="<?= $categoria["id"] ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </li>
            <?php endforeach ?>
        <?php endif ?>
    </ul>

</body>

</html>