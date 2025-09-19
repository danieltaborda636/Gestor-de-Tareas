<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Project.php";

$database = new Databasee();
$db = $database->getConnection();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Error: ID inválido.</p>";
    exit();
}

$id = (int)$_GET['id'];
$proyecto = Projects::buscar($id);
if (!$proyecto) {
    echo "<p>Error: Proyecto no encontrado.</p>";
    exit();
}
?>
<link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/editar.css">

<h2>Editar Proyecto</h2>

<form method="POST" action="controllers/ProjectController.php?action=update">
    <input type="hidden" name="id" value="<?= htmlspecialchars($proyecto['id']) ?>">

    <label>Nombre:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($proyecto['name']) ?>" required><br>

    <label>Descripción:</label>
    <textarea name="description"><?= htmlspecialchars($proyecto['description']) ?></textarea><br>

    <button type="submit">Actualizar</button>
</form>
