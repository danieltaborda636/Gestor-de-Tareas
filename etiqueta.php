<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Etiqueta</title>
</head>
<body>
    <form action="./views/Etiquetas/CrearEtiqueta.php" method="POST">
        <div>
            <label for="nombre">Nombre de la etiqueta:</label>
            <input type="text" id="nombre" name="nombre_etiqueta" required>
        </div>
        
        <div>
            <label for="color">Color de la etiqueta:</label>
            <input type="color" id="color" name="color" value="#000000" required>
        </div>

        <button type="submit">Crear Etiqueta</button>
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
</body>
</html>