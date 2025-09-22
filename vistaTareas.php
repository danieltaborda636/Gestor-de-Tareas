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
                                <a href="#" class="editar-btn" data-id="<?= $t['id'] ?>">Editar</a> |
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
