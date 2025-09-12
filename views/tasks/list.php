<?php
require_once __DIR__ . "/../../models/Task.php"; 

$tareas = Task::all(); // Obtiene todas las tareas como array asociativo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tareas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Listado de tareas</h2>

    <!-- Mostrar mensajes de error o éxito -->
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>

    <!-- Enlace para crear una nueva tarea -->
    <a href="create.php">Nueva tarea</a>

    <ul>
        <!-- Si hay tareas, las muestra en una lista -->
        <?php if (count($tareas) > 0): ?>
            <?php foreach ($tareas as $t): // Recorre cada tarea en $tareas ?>
                <li>
                    <!-- htmlspecialchars evita inyección de HTML al mostrar datos -->
                    <strong><?= htmlspecialchars($t['title']) ?></strong>
                    (<?= htmlspecialchars($t['status']) ?> | <?= htmlspecialchars($t['priority']) ?>)
                    <a href="edit.php?id=<?= $t['id'] ?>">✏️ Editar</a> <!-- Enlace a la vista de edición; pasa el id por GET -->
                    <!-- Formulario para eliminar la tarea; pasa id por GET -->
                    <form action="../../controllers/TaskController.php?action=delete&id=<?= $t['id'] ?>" method="POST" style="display:inline;"> 
                        <button type="submit" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">🗑️ Eliminar</button> 
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay tareas registradas.</p> <!-- Mensaje si no hay tareas -->
        <?php endif; ?> 
    </ul>
</body>
</html>