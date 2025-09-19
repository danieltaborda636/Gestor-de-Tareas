<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Task.php';

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
                    <li><a href="#"><i class="icon-admin"></i> Administración</a></li>
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
                <div class="user-actions">
                    <i class="icon-bell"></i>
                    <div class="menu-container">
                        <i class="icon-settings menu-icon" onclick="toggleMenu()"></i>
                        <div id="menu" class="menu">
                            <button type="button" onclick="abrirModal('modalModificar')">Modificar Perfil</button>
                            <div id="modalModificar" class="modal">
                                <div class="modal-contenido">
                                    <span class="cerrar" onclick="cerrarModal('modalModificar')">&times;</span>
                                    <h2>Modificar Perfil</h2>
                                    <form class="form-modificar" id="formModificar" 
                                        method="POST" action="controllers/UserController.php?action=update" 
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                        <label>Nombre:</label>
                                        <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                        <label>Correo:</label>
                                        <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                                        <label>Contraseña:</label>
                                        <input type="password" name="contrasena" placeholder="Nueva contraseña (opcional)">
                                        <label>Foto de perfil:</label>
                                        <input type="file" name="foto_perfil" accept="image/*">
                                        <button type="submit" class="submit-btn">Guardar cambios</button>
                                    </form>
                                </div>
                            </div>
                            <button type="button" onclick="abrirModal('modalPerfil')">Visualizar Perfil</button>
                            <div id="modalPerfil" class="modal">
                                <div class="modal-contenido">
                                    <span class="cerrar" onclick="cerrarModal('modalPerfil')">&times;</span>
                                    <h2>Perfil</h2>
                                    <h1>Foto de Perfil</h1>
                                    <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario" style="width:100px;height:100px;border-radius:50%;"><br>
                                    <h2>Nombre de Usuario</h2>
                                    <span><strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong></span>
                                    <h2>Correo de Usuario</h2>
                                    <p><?php echo htmlspecialchars($usuario['correo']); ?></p>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <script>
                        function toggleMenu() {
                            document.getElementById("menu").classList.toggle("active");
                        }
                        document.addEventListener("click", function(event) {
                            const menu = document.getElementById("menu");
                            const icon = document.querySelector(".menu-icon");
                            if (!menu.contains(event.target) && !icon.contains(event.target)) {
                                menu.classList.remove("active");
                            }
                        });
                        function abrirModal(id) {
                            document.getElementById(id).style.display = "flex";
                        }
                        function cerrarModal(id) {
                            document.getElementById(id).style.display = "none";
                        }
                    </script>
                    <div class="user-profile">
                        <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                        <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        <form action="logout.php" method="POST">
                            <button type="submit">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="overview-section">
                <h2>Resumen</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <p>Total de Tareas</p>
                        <span><?= $totalTareas ?></span>
                    </div>
                    <div class="stat-card">
                        <p>Completadas Esta Semana</p>
                        <span><?= $completadasSemana ?></span>
                    </div>
                    <div class="stat-card">
                        <p>Próximos Vencimientos</p>
                        <span><?= $proximosVencimientos ?></span>
                    </div>
                    <div class="project-overview-card">
                        <h3>Vista General del Proyecto</h3>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 95%;"></div>
                            </div>
                            <span>95%</span>
                        </div>
                        <p>Rediseño de Sitio Web</p>
                    </div>
                </div>
            </section>

            <section class="my-tasks-section">
                <button class="new-task-btn">
                    <a id="CrearTarea" href="http://localhost/proyecto-1/Gestor-de-Tareas/vistaCrearTareas.php">+ Crear Nueva Tarea</a>
                </button>
            </section>

            <section class="notifications-section">
                <h3>Notificaciones</h3>
                <div class="notification-item">
                    <p>Nuevo comentario en "Planificación del Proyecto"</p>
                    <span>hace 2 horas</span>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
