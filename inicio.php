<?php
session_start();
require_once __DIR__ . '/config/Database.php';

    if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];

        $database = new Databasee();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT usuario_id FROM sesiones WHERE token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $userModel = new User($db);
            $userData = $userModel->findByEmailOrId($row['usuario_id']);

            if ($userData) {
                $_SESSION['usuario_id'] = $userData['id'];
                $_SESSION['user'] = [
                    'id' => $userData['id'],
                    'nombre' => $userData['nombre_usuario'] ?? $userData['nombre'],
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
                <img src="http://localhost/proyecto-1/Gestor-de-Tareas/assets/uploads/logo.png" alt="Logo de Taskify"> <h1>TASKIFY</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li class="active"><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                    <li><a href="#"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                    <li><a href="#"><i class="icon-projects"></i> Proyectos</a></li>
                    <li><a href="#"><i class="icon-tags"></i> Etiquetas</a></li>
                    <li><a href="#"><i class="icon-history"></i> Historial</a></li>
                    <li><a href="#"><i class="icon-admin"></i> Administración</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="search-box">
                    <input type="text" placeholder="Buscar...">
                    <i class="icon-search"></i>
                </div>
                <div class="user-actions">
                    <i class="icon-bell"></i>
                    <i class="icon-settings"></i>
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
                        <span>253</span>
                    </div>
                    <div class="stat-card">
                        <p>Completadas Esta Semana</p>
                        <span>45</span>
                    </div>
                    <div class="stat-card">
                        <p>Próximos Vencimientos</p>
                        <span>5</span>
                    </div>
                    <div class="project-overview-card">
                        <h3>Vista General del Proyecto</h3>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 40%;"></div>
                            </div>
                            <span>40%</span>
                        </div>
                        <p>Rediseño de Sitio Web</p>
                    </div>
                </div>
            </section>

            <section class="my-tasks-section">
                <h2>Mis Tareas</h2>
                <button class="new-task-btn"><a id="CrearTarea" href="http://localhost/proyecto-1/Gestor-de-Tareas/views/tasks/create.php">+ Crear Nueva Tarea</a></button>

                <div class="tasks-board">
                    <div class="task-column">
                        <h3>Por Hacer</h3>
                        <div class="task-card">
                            <h4>Revisar Maquetas UI/UX</h4>
                            <p>Vence Dic 2023</p>
                            <span class="label design">Diseño</span>
                        </div>
                        </div>

                    <div class="task-column">
                        <h3>En Progreso</h3>
                        <div class="task-card">
                            <h4>Revisar Maquetas UI/XX</h4>
                            <p>Vence Dic 2023</p>
                            <span class="label in-progress">En Progreso</span>
                        </div>
                        </div>

                    <div class="task-column">
                        <h3>Hecho</h3>
                        <div class="task-card">
                            <h4>Tarea Completada</h4>
                            <p>Vence Dic 2023</p>
                            <span class="label done">Hecho</span>
                        </div>
                        </div>

                    <div class="task-column">
                        <h3>Archivado</h3>
                        <div class="task-card">
                            <h4>Tarea Archivada</h4>
                            <p>Vence Dic 2023</p>
                            <span class="label archived">Archivado</span>
                        </div>
                        </div>
                </div>
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
   <select>
    <option value="">bajar</option>
    <option value="">subir</option>
   </select>
</body>
</html>