<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';

if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $database = new Databasee();
    $db = $database->getConnection(); // esto es PDO

    // Buscar si existe el token en la tabla
    $stmt = $db->prepare("SELECT user_id FROM tokens WHERE token = :token LIMIT 1");
    $stmt->execute([":token" => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $userModel = new User($db);
        $userData = $userModel->getUserById($row['user_id']); // usamos getById en lugar de findByEmailOrId

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
                    <li><a href="#"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                    <li><a href="#"><i class="icon-projects"></i> Proyectos</a></li>
                    <li><a href="vistaEtiqueta.php"><i class="icon-tags"></i> Etiquetas</a></li>
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
                    <div class="menu-container">
                        <!-- Ícono de ajustes -->
                        <i class="icon-settings menu-icon" onclick="toggleMenu()"></i>

                        <!-- Menú flotante -->
                        <div id="menu" class="menu">

                            <!-- Botón para abrir el modal de modificar -->
                            <button type="button" onclick="abrirModal('modalModificar')">Modificar Perfil</button>

                            <!-- Modal Modificar Perfil -->
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

                            <!-- Botón para abrir el modal de perfil -->
                            <button type="button" onclick="abrirModal('modalPerfil')">Visualizar Perfil</button>

                            <!-- Modal Perfil -->
                            <div id="modalPerfil" class="modal">
                                <div class="modal-contenido">
                                    <span class="cerrar" onclick="cerrarModal('modalPerfil')">&times;</span>
                                    <h2>Perfil</h2>
                                    <!-- Foto -->
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

                        // Cerrar menú si se hace clic fuera
                        document.addEventListener("click", function(event) {
                            const menu = document.getElementById("menu");
                            const icon = document.querySelector(".menu-icon");

                            if (!menu.contains(event.target) && !icon.contains(event.target)) {
                                menu.classList.remove("active");
                            }
                        });

                        // Funciones para abrir/cerrar modales dinámicamente
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
                                <div class="progress-fill" style="width: 30%;"></div>
                            </div>
                            <span>30%</span>
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
</body>
</html>
