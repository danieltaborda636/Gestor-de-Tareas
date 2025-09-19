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

// --- Filtros de búsqueda ---
$filtros = [
    'q'           => $_GET['q'] ?? '',
    'project_id'  => $_GET['project_id'] ?? '',
    'priority'    => $_GET['priority'] ?? '',
    'status'      => $_GET['status'] ?? '',
    'assignee_id' => $_GET['assignee_id'] ?? '',
    'start_date'  => $_GET['start_date'] ?? '',
    'due_date'    => $_GET['due_date'] ?? ''
];

// Obtener tareas filtradas
$tareas = $taskModel->search($userId, $filtros);

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
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">

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
                        <!-- (Modal de perfil lo dejo igual que ya lo tenías) -->
                    </div>
                </div>
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
