<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../models/Project.php";
require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../models/Etiqueta.php";


// Conexión
$database = new Databasee();
$db = $database->getConnection();

// Instancias de modelos
$etiquetaModel = new Etiqueta($db);
$labels = $etiquetaModel->all();

// Obtener proyectos y usuarios para combos (consulta directa con PDO)
$projects = $db->query("SELECT id, name FROM projects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query("SELECT id, nombre_usuario FROM usuarios ORDER BY nombre_usuario")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva tarea</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Crear tarea</h2>

    <form method="POST" action="../../controllers/TaskController.php?action=create">
        <input type="text" name="title" placeholder="Título" required><br>

        <textarea name="description" placeholder="Descripción"></textarea><br>

        <label>Proyecto:</label>
        <select name="project_id">
            <option value="">-- Ninguno --</option>
            <?php foreach ($projects as $p): ?>
                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>Etiquetas:</label><br>
        <?php if (!empty($labels)): ?>
            <?php foreach ($labels as $l): ?>
                <label>
                    <input type="checkbox" name="labels[]" value="<?= htmlspecialchars($l['id']) ?>">
                    <span style="color: <?= htmlspecialchars($l['color']) ?>;">■</span>
                    <?= htmlspecialchars($l['nombre_etiqueta']) ?>
                </label><br>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay etiquetas disponibles.</p>
        <?php endif; ?>

        <label>Asignar a:</label>
        <select name="assignee_id">
            <option value="">-- Nadie --</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['nombre_usuario']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>Prioridad:</label>
        <select name="priority">
            <option value="low">Baja</option>
            <option value="medium" selected>Media</option>
            <option value="high">Alta</option>
            <option value="urgent">Urgente</option>
        </select><br>

        <label>Estado:</label>
        <select name="status">
            <option value="todo">Por hacer</option>
            <option value="in_progress">En progreso</option>
            <option value="done">Hecho</option>
            <option value="archived">Archivado</option>
        </select><br>

        <label>Fecha inicio:</label>
        <input type="date" name="start_date"><br>

        <label>Fecha vencimiento:</label>
        <input type="date" name="due_date"><br>

        <label>Recurrencia:</label>
        <select name="recurrence_rule">
            <option value="none">Ninguna</option>
            <option value="daily">Diaria</option>
            <option value="weekly">Semanal</option>
            <option value="monthly">Mensual</option>
        </select><br>

        <button type="submit">Guardar</button>
    </form>

    <a href="list.php">Volver</a>
</body>
</html>
