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
$usuario = $_SESSION['user'];
// Obtener proyectos, usuarios y etiquetas
$projects = $db->query("SELECT id, name FROM projects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query("SELECT id, nombre_usuario FROM usuarios ORDER BY nombre_usuario")->fetchAll(PDO::FETCH_ASSOC);
$labels = $etiquetaModel->getByUser($usuario['id'], $usuario['rol']);
$taskLabels = $etiquetaModel->getByTask($id);
$selected = array_column($taskLabels, 'id');
?>
<?php
// ===============================
// 🔑 Validar sesión con token remember_me
// ===============================
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $database = new Databasee();
    $db = $database->getConnection(); // esto es PDO

    $stmt = $db->prepare("SELECT usuario_id FROM tokens WHERE token = :token LIMIT 1");
    $stmt->execute([":token" => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $userModel = new User($db);
        $userData = $userModel->getUserById($row['usuario_id']);

        if ($userData) {
            $_SESSION['usuario_id'] = $userData['id'];
            $_SESSION['user'] = [
                'id' => $userData['id'],
                'nombre' => $userData['nombre_usuario'],
                'correo' => $userData['correo'],
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

// ===============================
// 📊 Estadísticas de tareas
// ===============================
$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);

$userId = $_SESSION['usuario_id'];

try {
    // ✅ Total de tareas creadas por este usuario
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tasks WHERE creator_id = :id");
    $stmt->execute([":id" => $userId]);
    $totalTareas = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // ✅ Completadas esta semana (status done o echo) usando completed_at o updated_at
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE creator_id = :id
          AND (status = 'done' OR status = 'echo')
          AND (
                (completed_at IS NOT NULL AND YEARWEEK(completed_at, 1) = YEARWEEK(CURDATE(), 1))
             OR (completed_at IS NULL AND updated_at IS NOT NULL AND YEARWEEK(updated_at, 1) = YEARWEEK(CURDATE(), 1))
          )
    ");
    $stmt->execute([":id" => $userId]);
    $completadasSemana = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // ✅ Próximos vencimientos (siguientes 7 días)
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tasks 
        WHERE creator_id = :id
          AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute([":id" => $userId]);
    $proximosVencimientos = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

} catch (PDOException $e) {
    error_log("Error estadísticas inicio.php: " . $e->getMessage());
    $totalTareas = $completadasSemana = $proximosVencimientos = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control de Taskify</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/editar.css">

   

    <!-- Bootstrap JS (necesario para el modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
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
                        <li><a href="./vistaUsuarios.php"><i class="icon-admin"></i> Administración</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Contenedor principal -->
        <div class="main-content">
            <!-- Header -->
            <header>
                <div class="user-profile">
                    <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                    <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    <form action="logout.php" method="POST">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </header>

            <!-- Formulario debajo del header -->
            <section class="form-section">
                <h2>Editar tarea</h2>
                <form method="POST" action="controllers/TaskController.php?action=update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tarea['id']) ?>">

                    <label>Título:</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($tarea['title']) ?>" required>

                    <label>Descripción:</label>
                    <textarea name="description"><?= htmlspecialchars($tarea['description']) ?></textarea>

                    <label>Proyecto:</label>
                    <select name="project_id">
                        <option value="">-- Ninguno --</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($tarea['project_id'] == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Etiquetas:</label>
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
                    </select>

                    <label>Prioridad:</label>
                    <select name="priority">
                        <option value="low" <?= $tarea['priority'] == 'low' ? 'selected' : '' ?>>Baja</option>
                        <option value="medium" <?= $tarea['priority'] == 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="high" <?= $tarea['priority'] == 'high' ? 'selected' : '' ?>>Alta</option>
                        <option value="urgent" <?= $tarea['priority'] == 'urgent' ? 'selected' : '' ?>>Urgente</option>
                    </select>

                    <label>Estado:</label>
                    <select name="status">
                        <option value="todo" <?= $tarea['status'] == 'todo' ? 'selected' : '' ?>>Por hacer</option>
                        <option value="in_progress" <?= $tarea['status'] == 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                        <option value="done" <?= $tarea['status'] == 'done' ? 'selected' : '' ?>>Hecho</option>
                        <option value="archived" <?= $tarea['status'] == 'archived' ? 'selected' : '' ?>>Archivado</option>
                    </select>

                    <label>Fecha inicio:</label>
                    <input type="date" name="start_date" value="<?= !empty($tarea['start_date']) ? date('Y-m-d', strtotime($tarea['start_date'])) : '' ?>">

                    <label>Fecha vencimiento:</label>
                    <input type="date" name="due_date" value="<?= !empty($tarea['due_date']) ? date('Y-m-d', strtotime($tarea['due_date'])) : '' ?>">

                    <label>Recurrencia:</label>
                    <select name="recurrence_rule">
                        <option value="none" <?= $tarea['recurrence_rule'] == 'none' ? 'selected' : '' ?>>Ninguna</option>
                        <option value="daily" <?= $tarea['recurrence_rule'] == 'daily' ? 'selected' : '' ?>>Diaria</option>
                        <option value="weekly" <?= $tarea['recurrence_rule'] == 'weekly' ? 'selected' : '' ?>>Semanal</option>
                        <option value="monthly" <?= $tarea['recurrence_rule'] == 'monthly' ? 'selected' : '' ?>>Mensual</option>
                    </select>

                    <button type="submit">Actualizar</button>
                </form>
            </section>
        </div>
    </div>
</body>


           
    
            


