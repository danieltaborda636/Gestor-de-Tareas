<?php
require_once __DIR__ . "/../../models/Project.php"; 

$proyectos = Projects::all(); // Obtiene todas las tareas como array asociativo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Proyectos</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Listado de Proyectos</h2>

    <!-- Mostrar mensajes de error o éxito -->
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>

    <!-- Enlace para crear una nueva tarea -->
    <a href="create.php">Nueva Proyecto</a>

    <ul>
        <!-- Si hay tareas, las muestra en una lista -->
        <?php if (count($proyectos) > 0): ?>
            <?php foreach ($proyectos as $p): // Recorre cada tarea en $tareas ?>
                <li>
                    <!-- htmlspecialchars evita inyección de HTML al mostrar datos -->
                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                    (<?= htmlspecialchars($p['description']) ?>)
                    <a href="edit.php?id=<?= $p['id'] ?>">✏️ Editar</a> <!-- Enlace a la vista de edición; pasa el id por GET -->
                    <!-- Formulario para eliminar el proyecto; pasa id por GET -->
                    <form action="../../controllers/ProjectController.php?action=delete&id=<?= $p['id'] ?>" method="POST" style="display:inline;"> 
                        <button type="submit" onclick="return confirm('¿Seguro que deseas eliminar este proyecto?');">🗑️ Eliminar</button> 
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay proyectos registrados.</p> <!-- Mensaje si no hay proyectos -->
        <?php endif; ?> 
    </ul>
</body>
</html>