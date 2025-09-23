<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Project.php";
require_once __DIR__ . "/models/User.php";
require_once __DIR__ . "/models/Etiqueta.php";

// Conexión
$database = new Databasee();
$db = $database->getConnection();

// Validar cookie "recordarme"
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $stmt = $db->prepare("SELECT user_id FROM tokens WHERE token = :token LIMIT 1");
    $stmt->execute([":token" => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $userModel = new User($db);
        $userData = $userModel->getUserById($row['user_id']);

        if ($userData) {
            $_SESSION['usuario_id'] = $userData['id'];
            $_SESSION['user'] = [
                'id' => $userData['id'],
                'nombre' => $userData['nombre_usuario'],
                'correo' => $userData['correo'],
                'rol' => $userData['rol'] ?? 'miembro',
                'foto_perfil' => !empty($userData['foto_perfil']) ? $userData['foto_perfil'] : 'assets/uploads/default.jpeg'
            ];
        }
    }
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['user'];

// Instancias de modelos
$etiquetaModel = new Etiqueta($db);

// ✅ Filtrar proyectos y etiquetas por rol
$projects = Projects::getByUser($usuario['id'], $usuario['rol']);
$labels   = $etiquetaModel->getByUser($usuario['id'], $usuario['rol']);

// Solo cargamos la lista de usuarios si el que está logueado es admin
$users = [];
if ($usuario['rol'] === 'admin') {
    $users = $db->query("SELECT id, nombre_usuario FROM usuarios ORDER BY nombre_usuario")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva tarea</title>
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
                <li class="active"><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                <li><a href="vistaTareas.php"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                <li><a href="vistaProyectos.php"><i class="icon-projects"></i> Proyectos</a></li>
                <li><a href="vistaEtiqueta.php"><i class="icon-tags"></i> Etiquetas</a></li>
                <li><a href="historial.php"><i class="icon-history"></i> Historial</a></li>
                <?php if ($usuario['rol'] === 'admin'): ?>
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

        <h2>Crear tarea</h2>

        <form method="POST" action="controllers/TaskController.php?action=create">
            <!-- ✅ creator_id obligatorio -->
            <input type="hidden" name="creator_id" value="<?= htmlspecialchars($usuario['id']) ?>">

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

            <!-- ✅ Solo admin puede asignar a otros -->
            <?php if ($usuario['rol'] === 'admin'): ?>
                <label>Asignar a:</label>
                <select name="assignee_id">
                    <option value="">-- Nadie --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['nombre_usuario']) ?></option>
                    <?php endforeach; ?>
                </select><br>
            <?php else: ?>
                <!-- Miembro: la tarea queda sin asignado o se le asigna a sí mismo -->
                <input type="hidden" name="assignee_id" value="">
            <?php endif; ?>

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

        <br>
        <a href="vistaTareas.php" class="button-link">⬅ Volver</a>
    </main>
</div>
</body>
</html>
