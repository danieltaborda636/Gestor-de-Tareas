<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Task.php";
require_once __DIR__ . "/models/User.php";

session_start(); // Primero la sesión

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=Debes iniciar sesión");
    exit;
}

$userId = $_SESSION['usuario_id'];

$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);

// Obtener todas las tareas del usuario
$tareas = $taskModel->getByUser($userId);

// Validar sesión con cookie remember_me
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
    <title>Mis tareas</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS (necesario para el modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Estilos del modal */
        .modal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 8px;
            position: relative;
        }
        .modal-content .cerrar {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 22px;
            cursor: pointer;
        }
    </style>
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
                <input type="text" placeholder="Buscar...">
                <i class="icon-search"></i>
            </div>
            <div class="user-actions">
                   <!-- Botón de notificaciones -->
                <button id="btnNotificaciones" class="btn btn-light">
                    <i class="icon-bell"></i>
                </button>

                    <!-- Modal -->
                    <div class="modal fade" id="modalNotificaciones" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Notificaciones de tareas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body" id="contenidoNotificaciones">
                            <!-- Aquí cargaremos las tareas mediante AJAX -->
                        </div>
                        </div>
                    </div>
                    </div>

                        <script>
                        $(document).ready(function(){
                            $("#btnNotificaciones").click(function(){
                                $.ajax({
                                    url: "notificacion.php",
                                    method: "GET",
                                    success: function(data){
                                        $("#contenidoNotificaciones").html(data);
                                        $("#modalNotificaciones").modal("show");
                                    },
                                    error: function(){
                                        $("#contenidoNotificaciones").html("<p>Error al cargar las notificaciones.</p>");
                                        $("#modalNotificaciones").modal("show");
                                    }
                                });
                            });
                        });
                        </script>
                <div class="menu-container">
                    <i class="icon-settings menu-icon" onclick="toggleMenu()"></i>
                    <div id="menu" class="menu">
                        <button type="button" onclick="abrirModal('modalModificar')">Modificar Perfil</button>

                        <!-- Modal Modificar Perfil -->
                        <div id="modalModificar" class="modal">
                            <div class="modal-content">
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

                        <!-- Modal Perfil -->
                        <div id="modalPerfil" class="modal">
                            <div class="modal-content">
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
                        <th>Fecha inicio</th>
                        <th>Fecha vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['title']) ?></td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= htmlspecialchars($t['project_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($t['priority']) ?></td>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                            <td><?= !empty($t['start_date']) ? date('d-m-Y', strtotime($t['start_date'])) : '' ?></td>
                            <td><?= !empty($t['due_date']) ? date('d-m-Y', strtotime($t['due_date'])) : '' ?></td>
                            <td>
                                <a href="verTarea.php?id=<?= $t['id'] ?>">Ver</a> |
                                <a href="#" class="editar-btn" data-id="<?= $t['id'] ?>">Editar</a> |
                                <a href="controllers/TaskController.php?action=delete&id=<?= $t['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta tarea?')">Eliminar</a>
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

<!-- Modal para editar tareas -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="cerrar" onclick="cerrarModal('modalEditar')">&times;</span>
        <div id="contenidoModal"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const botonesEditar = document.querySelectorAll(".editar-btn");
    const modal = document.getElementById("modalEditar");
    const contenido = document.getElementById("contenidoModal");

    botonesEditar.forEach(boton => {
        boton.addEventListener("click", e => {
            e.preventDefault();
            const id = boton.getAttribute("data-id");

            fetch("editar.php?id=" + id)
                .then(res => res.text())
                .then(html => {
                    contenido.innerHTML = html;
                    modal.style.display = "flex";
                })
                .catch(() => {
                    contenido.innerHTML = "<p>Error al cargar el formulario.</p>";
                    modal.style.display = "flex";
                });
        });
    });
});

</script>

</body>
</html>
