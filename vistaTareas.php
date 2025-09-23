<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Task.php";
require_once __DIR__ . "/models/User.php";

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php?error=Debes iniciar sesión");
    exit;
}

$usuario = $_SESSION['user'];
$userId  = $usuario['id'];
$rol     = $usuario['rol'] ?? 'miembro';

$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);

$filtros = [
    "q"          => $_GET['q'] ?? '',
    "project_id" => $_GET['project_id'] ?? '',
    "priority"   => $_GET['priority'] ?? '',
    "status"     => $_GET['status'] ?? '',
    "assignee_id"=> $_GET['assignee_id'] ?? '',
    "start_date" => $_GET['start_date'] ?? '',
    "due_date"   => $_GET['due_date'] ?? '',
];

$tareas = $taskModel->search($userId, $filtros, $rol);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis tareas</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
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
                <li class="active"><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                <li><a href="vistaTareas.php"><i class="icon-mytasks"></i> Mis Tareas</a></li>
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
            <div class="search-box">
                <form method="GET" action="vistaTareas.php">
                    <input type="text" name="q" placeholder="Buscar..." style="width: 150px;" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <i class="icon-search"></i>

                    <select name="priority" class="busquedaSeleccion">
                        <option value="">-- Prioridad --</option>
                        <option value="low" <?= ($_GET['priority'] ?? '') == 'low' ? 'selected' : '' ?>>Baja</option>
                        <option value="medium" <?= ($_GET['priority'] ?? '') == 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="high" <?= ($_GET['priority'] ?? '') == 'high' ? 'selected' : '' ?>>Alta</option>
                        <option value="urgent" <?= ($_GET['priority'] ?? '') == 'urgent' ? 'selected' : '' ?>>Urgente</option>
                    </select>

                    <select name="status" class="busquedaSeleccion">
                        <option value="">-- Estado --</option>
                        <option value="todo" <?= ($_GET['status'] ?? '') == 'todo' ? 'selected' : '' ?>>Por hacer</option>
                        <option value="in_progress" <?= ($_GET['status'] ?? '') == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                        <option value="done" <?= ($_GET['status'] ?? '') == 'done' ? 'selected' : '' ?>>Hecho</option>
                        <option value="archived" <?= ($_GET['status'] ?? '') == 'archived' ? 'selected' : '' ?>>Archivado</option>
                    </select>

                    <label style="margin-left: 5px;">Desde:</label>
                    <input type="date" name="start_date" style="width: 150px; margin-left: 5px;" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                    <label style="margin-left: 5px;">Hasta:</label>
                    <input type="date" name="due_date" style="width: 150px; margin-left: 5px;" value="<?= htmlspecialchars($_GET['due_date'] ?? '') ?>">

                    <button type="submit" style="margin-left: 5px;">Filtrar</button>
                    <a href="vistaTareas.php" style="margin-left: 5px;">Limpiar</a>
                </form>
            </div>
            <!-- Botón de notificaciones -->
            <button id="btnNotificaciones" class="btn btn-light">
                <i class="icon-bell"></i>
            </button>
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                <form action="logout.php" method="POST">
                    <button type="submit">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <h2>Mis Tareas</h2>

        <?php if (!empty($tareas)): ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Proyecto</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Inicio</th>
                        <th>Vencimiento</th>
                        <th>Creador</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $t): ?>
                        <tr>
                            <td>
                                <?php if (!empty($t['parent_task_id'])): ?>
                                    🔹 <em><?= htmlspecialchars($t['title']) ?></em>
                                    <br><small>Subtarea de: <?= htmlspecialchars($t['parent_title'] ?? '—') ?></small>
                                <?php else: ?>
                                    <?= htmlspecialchars($t['title']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= htmlspecialchars($t['project_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($t['priority']) ?></td>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                            <td><?= !empty($t['start_date']) ? date('d-m-Y', strtotime($t['start_date'])) : '' ?></td>
                            <td><?= !empty($t['due_date']) ? date('d-m-Y', strtotime($t['due_date'])) : '' ?></td>
                            <td><?= htmlspecialchars($t['creador_nombre'] ?? '—') ?></td>
                            <td>
                                <a href="verTarea.php?id=<?= $t['id'] ?>">Ver</a> |
                                <a href="editar.php?id=<?= $t['id'] ?>" class="editar-btn" data-id="<?= $t['id'] ?>">Editar</a> |
                                <?php if ($rol === 'admin' || $t['creator_id'] == $userId): ?>
                                    <a href="controllers/TaskController.php?action=delete&id=<?= $t['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?')">Eliminar</a>
                                <?php else: ?>
                                    <span style="color: gray;">No permitido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes tareas aún.</p>
        <?php endif; ?>

        <br>
        <a href="vistaCrearTareas.php" class="button-link">+ Nueva tarea</a>
        <a href="javascript:history.back()" class="button-link">Volver</a>
    </main>
</div>
</body>
</html>
