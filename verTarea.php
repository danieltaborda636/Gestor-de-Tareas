<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Task.php";
require_once __DIR__ . "/models/Comment.php";
require_once __DIR__ . "/models/User.php";

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Debes iniciar sesión");
    exit;
}

$usuario = $_SESSION['user'];
$userId  = $usuario['id'];
$rol     = $usuario['rol'] ?? 'miembro';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: vistaTareas.php?error=ID inválido");
    exit;
}

$database = new Databasee();
$db = $database->getConnection();

$taskModel    = new Task($db);
$commentModel = new Comment($db);

// Buscar tarea
$task = $taskModel->find($_GET['id']);
if (!$task) {
    header("Location: vistaTareas.php?error=Tarea no encontrada");
    exit;
}

// Subtareas
$subtasks = $taskModel->getSubtasks($task['id']);

// Tarea padre
$parentTask = null;
if (!empty($task['parent_task_id'])) {
    $parentTask = $taskModel->find($task['parent_task_id']);
}

// Comentarios
$comments = $commentModel->allByTask($task['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de tarea</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/vistaTareas.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo">
            <img src="http://localhost/proyecto-1/Gestor-de-Tareas/assets/uploads/logo.png" alt="Logo de Taskify"> 
            <h1>TASKIFY</h1>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                <li class="active"><a href="vistaTareas.php"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                <li><a href="vistaProyectos.php"><i class="icon-projects"></i> Proyectos</a></li>
                <li><a href="vistaEtiqueta.php"><i class="icon-tags"></i> Etiquetas</a></li>
                <li><a href="historial.php"><i class="icon-history"></i> Historial</a></li>
                <?php if ($rol === 'admin'): ?>
                    <li><a href="#"><i class="icon-admin"></i> Administración</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                <form action="logout.php" method="POST">
                    <button type="submit">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <h2>Detalle de Tarea</h2>

        <h3>
            <?= htmlspecialchars($task['title']) ?>
            <?php if (!empty($task['parent_task_id']) && $parentTask): ?>
                <br><small>🔹 Subtarea de: 
                    <a href="verTarea.php?id=<?= $parentTask['id'] ?>"><?= htmlspecialchars($parentTask['title']) ?></a>
                </small>
            <?php endif; ?>
        </h3>

        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($task['description'])) ?></p>
        <p><strong>Proyecto:</strong> <?= htmlspecialchars($task['proyecto_nombre'] ?? "—") ?></p>
        <p><strong>Asignada a:</strong> <?= htmlspecialchars($task['asignado_nombre'] ?? "Nadie") ?></p>
        <p><strong>Prioridad:</strong> <?= htmlspecialchars($task['priority']) ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars($task['status']) ?></p>
        <p><strong>Inicio:</strong> <?= $task['start_date'] ? date("d-m-Y", strtotime($task['start_date'])) : "—" ?></p>
        <p><strong>Vencimiento:</strong> <?= $task['due_date'] ? date("d-m-Y", strtotime($task['due_date'])) : "—" ?></p>
        <?php if ($rol === 'admin'): ?>
            <p><strong>Creador:</strong> <?= htmlspecialchars($task['creador_nombre'] ?? "—") ?></p>
        <?php endif; ?>

        <!-- Botones de acciones sobre la tarea principal -->
        <p>
            <a href="editar.php?id=<?= $task['id'] ?>" 
            style="color:blue; margin-right:10px;">✏️ Editar tarea</a>

            <?php if ($rol === 'admin' || $task['creator_id'] == $userId): ?>
                <a href="controllers/TaskController.php?action=delete&id=<?= $task['id'] ?>" 
                onclick="return confirm('¿Seguro que deseas eliminar esta tarea?')"
                style="color:red;">🗑 Eliminar esta tarea</a>
            <?php endif; ?>
        </p>

        <hr>
        <h3>Subtareas</h3>

        <?php if (empty($task['parent_task_id'])): ?>
            <form method="POST" action="controllers/TaskController.php?action=create">
                <input type="hidden" name="parent_task_id" value="<?= $task['id'] ?>">
                <input type="hidden" name="creator_id" value="<?= $userId ?>">
                <label>Título:</label>
                <input type="text" name="title" required>
                <label>Descripción:</label>
                <input type="text" name="description">
                <label>Prioridad:</label>
                <select name="priority">
                    <option value="low">Baja</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente</option>
                </select>
                <button type="submit">Agregar Subtarea</button>
            </form>
        <?php else: ?>
            <p><em>No se pueden crear subtareas de subtareas.</em></p>
        <?php endif; ?>

        <?php if (!empty($subtasks)): ?>
            <ul>
                <?php foreach ($subtasks as $s): ?>
                    <li class="subtareas">
                        <strong><?= htmlspecialchars($s['title']) ?></strong> (<?= htmlspecialchars($s['status']) ?>) <br>
                        <small>
                            Proyecto: <?= htmlspecialchars($s['proyecto_nombre'] ?? "—") ?> |
                            Asignado a: <?= htmlspecialchars($s['asignado_nombre'] ?? "Nadie") ?> |
                            Creador: <?= htmlspecialchars($s['creador_nombre'] ?? "—") ?>
                        </small><br>
                        <a href="verTarea.php?id=<?= $s['id'] ?>">Ver</a>
                        <?php if ($rol === 'admin' || $s['creator_id'] == $userId): ?>
                            <a href="controllers/TaskController.php?action=delete&id=<?= $s['id'] ?>" 
                               onclick="return confirm('¿Eliminar subtarea?')">Eliminar</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay subtareas aún.</p>
        <?php endif; ?>

        <hr>
        <h3>Comentarios</h3>

        <form method="POST" action="controllers/CommentController.php?action=create">
            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
            <textarea name="body" placeholder="Escribe un comentario..." required></textarea><br>
            <button type="submit">Agregar comentario</button>
        </form>

        <?php if (!empty($comments)): ?>
            <ul class="comentarios">
                <?php foreach ($comments as $c): ?>
                    <li>
                        <strong><?= htmlspecialchars($c['nombre_usuario']) ?>:</strong> 
                        <?= htmlspecialchars($c['body']) ?>
                        <em>(<?= date("d-m-Y H:i", strtotime($c['created_at'])) ?>)</em>
                        <?php if ($c['user_id'] == $userId): ?>
                            <!-- Botón editar -->
                            <a href="controllers/CommentController.php?action=edit&id=<?= $c['id'] ?>&task_id=<?= $task['id'] ?>">Editar</a> 
                            <!-- Botón eliminar -->
                            <a href="controllers/CommentController.php?action=delete&id=<?= $c['id'] ?>&task_id=<?= $task['id'] ?>" 
                               onclick="return confirm('¿Seguro que deseas eliminar este comentario?')">Eliminar</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay comentarios aún.</p>
        <?php endif; ?>

        <br>
        <a href="vistaTareas.php">⬅ Volver a mis tareas</a>
    </main>
</div>
</body>
</html>
