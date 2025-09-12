<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva tarea</title>
</head>
<body>
    <h2>Crear tarea</h2>

    <!-- Formulario que envía datos por POST al controlador con action=create -->
    <form method="POST" action="../../controllers/TaskController.php?action=create">
        <!-- Campo de texto para el título (requerido) -->
        <input type="text" name="title" placeholder="Título" required><br>

        <!-- Textarea para la descripción -->
        <textarea name="description" placeholder="Descripción"></textarea><br>

        <!-- Selección de prioridad -->
        <label>Prioridad:</label>
        <select name="priority">
            <option value="low">Baja</option>
            <option value="medium" selected>Media</option>
            <option value="high">Alta</option>
            <option value="urgent">Urgente</option>
        </select><br>

        <!-- Selección del estado -->
        <label>Estado:</label>
        <select name="status">
            <option value="todo">Por hacer</option>
            <option value="in_progress">En progreso</option>
            <option value="done">Hecho</option>
            <option value="archived">Archivado</option>
        </select><br>

        <!-- Botón para enviar el formulario -->
        <button type="submit">Guardar</button>
    </form>

    <!-- Enlace para volver al listado -->
    <a href="list.php">Volver</a>
</body>
</html>
