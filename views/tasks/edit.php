<?php
require_once __DIR__ . "/../../models/Task.php"; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php?error=ID inválido");
    exit();
}

$tarea = Task::buscar($_GET['id']); // Busca la tarea por id 
if (!$tarea) {
    header("Location: list.php?error=Tarea no encontrada");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar tarea</title>
</head>
<body>
    <h2>Editar tarea</h2>

    <!-- Formulario que envía por POST al controlador con action=update -->
    <form method="POST" action="../../controllers/TaskController.php?action=update">
        <!-- Campo oculto con el id de la tarea -->
        <input type="hidden" name="id" value="<?= $tarea['id'] ?>">

        <!-- Título (se muestra el valor actual escapado con htmlspecialchars) -->
        <input type="text" name="title" value="<?= htmlspecialchars($tarea['title']) ?>" required><br>

        <!-- Descripción -->
        <textarea name="description"><?= htmlspecialchars($tarea['description']) ?></textarea><br>

        <!-- Prioridad: se marca el selected según el valor actual -->
        <label>Prioridad:</label>
        <select name="priority">
            <option value="low" <?= $tarea['priority'] == 'low' ? 'selected' : '' ?>>Baja</option>
            <option value="medium" <?= $tarea['priority'] == 'medium' ? 'selected' : '' ?>>Media</option>
            <option value="high" <?= $tarea['priority'] == 'high' ? 'selected' : '' ?>>Alta</option>
            <option value="urgent" <?= $tarea['priority'] == 'urgent' ? 'selected' : '' ?>>Urgente</option>
        </select><br>

        <!-- Estado: idem -->
        <label>Estado:</label>
        <select name="status">
            <option value="todo" <?= $tarea['status'] == 'todo' ? 'selected' : '' ?>>Por hacer</option>
            <option value="in_progress" <?= $tarea['status'] == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
            <option value="done" <?= $tarea['status'] == 'done' ? 'selected' : '' ?>>Hecho</option>
            <option value="archived" <?= $tarea['status'] == 'archived' ? 'selected' : '' ?>>Archivado</option>
        </select><br>

        <!-- Botón para actualizar -->
        <button type="submit">Actualizar</button>
    </form>

    <a href="list.php">Volver</a>
</body>
</html>
