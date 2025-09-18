<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../models/Task.php";
require_once __DIR__ . "/../../models/Etiqueta.php";


$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);
$etiquetaModel = new Etiqueta($db);

$tareas = $taskModel->all();
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

    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <p style="color: green;"><?= htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?></p>
    <?php endif; ?>

    <a href="create.php">Nueva tarea</a>

    <ul>
        <?php if ($tareas && count($tareas) > 0): ?>
            <?php foreach ($tareas as $t): ?>
                <li>
                    <strong><?= htmlspecialchars($t['title']) ?></strong>
                    (<?= htmlspecialchars($t['status']) ?> | <?= htmlspecialchars($t['priority']) ?>)
                    <?php if (!empty($t['project'])): ?>
                        - Proyecto: <?= htmlspecialchars($t['project']) ?>
                    <?php endif; ?>
                    <?php if (!empty($t['assignee'])): ?>
                        - Asignado a: <?= htmlspecialchars($t['assignee']) ?>
                    <?php endif; ?>
                    <br>
                    Etiquetas:
                    <?php
                        $taskLabels = $etiquetaModel->getByTask($t['id']);
                        if (!empty($taskLabels)) {
                            foreach ($taskLabels as $lbl) {
                                $name = htmlspecialchars($lbl['nombre_etiqueta']);
                                $color = htmlspecialchars($lbl['color'] ?? '#000');
                                echo "<span style=\"display:inline-block;padding:2px 6px;margin-right:6px;border-radius:4px;border:1px solid #ddd;color:{$color};\">{$name}</span>";
                            }
                        } else {
                            echo "Sin etiquetas";
                        }
                    ?>
                    <br>
                    <a href="edit.php?id=<?= htmlspecialchars($t['id']) ?>">✏️ Editar</a>
                    <form action="../../controllers/TaskController.php?action=delete&id=<?= htmlspecialchars($t['id']) ?>" method="POST" style="display:inline;">
                        <button type="submit" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?');">🗑️ Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay tareas registradas.</p>
        <?php endif; ?>
    </ul>
</body>
</html>
