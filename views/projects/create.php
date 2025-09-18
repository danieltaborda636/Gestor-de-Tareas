<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Proyecto</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Crear Proyecto</h2>

    <!-- Formulario que envía datos por POST al controlador con action=create -->
    <form method="POST" action="../../controllers/ProjectController.php?action=create">
        <!-- Campo de texto para el título (requerido) -->
        <input type="text" name="name" placeholder="Nombre" required><br>

        <!-- Textarea para la descripción -->
        <textarea name="description" placeholder="Descripción"></textarea><br>

        <!-- Botón para enviar el formulario -->
        <button type="submit">Guardar</button>
    </form>

    <!-- Enlace para volver al listado -->
    <a href="list.php">Volver</a>
</body>
</html>
