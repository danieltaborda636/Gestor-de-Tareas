<?php
require_once __DIR__ . "/../../models/Project.php"; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php?error=ID inválido");
    exit();
}

$proyecto = Projects::buscar($_GET['id']); // Busca la tarea por id 
if (!$proyecto) {
    header("Location: list.php?error=Proyecto no encontrada");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proyecto</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Editar Proyecto</h2>

    <!-- Formulario que envía por POST al controlador con action=update -->
    <form method="POST" action="../../controllers/ProjectController.php?action=update">
        <!-- Campo oculto con el id de la tarea -->
        <input type="hidden" name="id" value="<?= $proyecto['id'] ?>">

        <!-- Título (se muestra el valor actual escapado con htmlspecialchars) -->
        <input type="text" name="name" value="<?= htmlspecialchars($proyecto['name']) ?>" required><br>

        <!-- Descripción -->
        <textarea name="description"><?= htmlspecialchars($proyecto['description']) ?></textarea><br>

        <!-- Botón para actualizar -->
        <button type="submit">Actualizar</button>
    </form>

    <a href="list.php">Volver</a>
</body>
</html>