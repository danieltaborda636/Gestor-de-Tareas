<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Task.php";
require_once __DIR__ . "/models/Project.php";
require_once __DIR__ . "/models/User.php";
require_once __DIR__ . "/models/Etiqueta.php";

$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);
$etiquetaModel = new Etiqueta($db);

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Error: ID inválido.</p>";
    exit();
}

$id = (int)$_GET['id'];
$tarea = $taskModel->find($id);
if (!$tarea) {
    echo "<p>Error: Tarea no encontrada.</p>";
    exit();
}

// Obtener proyectos, usuarios y etiquetas
$projects = $db->query("SELECT id, name FROM projects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query("SELECT id, nombre_usuario FROM usuarios ORDER BY nombre_usuario")->fetchAll(PDO::FETCH_ASSOC);
$labels = $etiquetaModel->all();
$taskLabels = $etiquetaModel->getByTask($id);
$selected = array_column($taskLabels, 'id');
?>
<link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/editar.css">

<h2>Editar tarea</h2>

<form method="POST" action="controllers/TaskController.php?action=update">
    <input type="hidden" name="id" value="<?= htmlspecialchars($tarea['id']) ?>">

    <label>Título:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($tarea['title']) ?>" required><br>

    <label>Descripción:</label>
    <textarea name="description"><?= htmlspecialchars($tarea['description']) ?></textarea><br>

    <label>Proyecto:</label>
    <select name="project_id">
        <option value="">-- Ninguno --</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($tarea['project_id'] == $p['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Etiquetas:</label><br>
    <?php if (!empty($labels)): ?>
        <?php foreach ($labels as $l): ?>
            <label>
                <input type="checkbox" name="labels[]" value="<?= htmlspecialchars($l['id']) ?>"
                    <?= in_array($l['id'], $selected) ? 'checked' : '' ?>>
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
            <option value="<?= htmlspecialchars($u['id']) ?>" <?= ($tarea['assignee_id'] == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nombre_usuario']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Prioridad:</label>
    <select name="priority">
        <option value="low" <?= $tarea['priority'] == 'low' ? 'selected' : '' ?>>Baja</option>
        <option value="medium" <?= $tarea['priority'] == 'medium' ? 'selected' : '' ?>>Media</option>
        <option value="high" <?= $tarea['priority'] == 'high' ? 'selected' : '' ?>>Alta</option>
        <option value="urgent" <?= $tarea['priority'] == 'urgent' ? 'selected' : '' ?>>Urgente</option>
    </select><br>

    <label>Estado:</label>
    <select name="status">
        <option value="todo" <?= $tarea['status'] == 'todo' ? 'selected' : '' ?>>Por hacer</option>
        <option value="in_progress" <?= $tarea['status'] == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
        <option value="done" <?= $tarea['status'] == 'done' ? 'selected' : '' ?>>Hecho</option>
        <option value="archived" <?= $tarea['status'] == 'archived' ? 'selected' : '' ?>>Archivado</option>
    </select><br>

    <label>Fecha inicio:</label>
    <input type="date" name="start_date" value="<?= !empty($tarea['start_date']) ? date('Y-m-d', strtotime($tarea['start_date'])) : '' ?>"><br>

    <label>Fecha vencimiento:</label>
    <input type="date" name="due_date" value="<?= !empty($tarea['due_date']) ? date('Y-m-d', strtotime($tarea['due_date'])) : '' ?>"><br>

    <label>Recurrencia:</label>
    <select name="recurrence_rule">
        <option value="none" <?= $tarea['recurrence_rule'] == 'none' ? 'selected' : '' ?>>Ninguna</option>
        <option value="daily" <?= $tarea['recurrence_rule'] == 'daily' ? 'selected' : '' ?>>Diaria</option>
        <option value="weekly" <?= $tarea['recurrence_rule'] == 'weekly' ? 'selected' : '' ?>>Semanal</option>
        <option value="monthly" <?= $tarea['recurrence_rule'] == 'monthly' ? 'selected' : '' ?>>Mensual</option>
    </select><br>

    <button type="submit">Actualizar</button>
</form>
