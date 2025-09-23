<?php
require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/Project.php";

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=Debes iniciar sesión");
    exit;
}

$usuario = $_SESSION['user'];

// Obtener todos los proyectos
$proyectos = Projects::getByUser($usuario['id'], $usuario['rol']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Proyectos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">

    <style>
        /* Reutilizo estilos del modal */
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
             <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS (necesario para el modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <h1>TASKIFY</h1>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                <li><a href="vistaTareas.php"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                <li class="active"><a href="vistaProyectos.php"><i class="icon-projects"></i> Proyectos</a></li>
                <li><a href="vistaEtiqueta.php"><i class="icon-tags"></i> Etiquetas</a></li>
                <li><a href="historial.php"><i class="icon-history"></i> Historial</a></li>
                <li><a href="#"><i class="icon-admin"></i> Administración</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
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
                <div class="user-profile">
                    <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                    <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    <form action="logout.php" method="POST">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </header>

        <h2>Mis Proyectos</h2>

        <?php if (!empty($proyectos)): ?>
            <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha creación</th>
                        <?php if ($usuario['rol'] === 'admin'): ?>
                            <th>Creador</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['description']) ?></td>
                            <td><?= !empty($p['created_at']) ? date('d-m-Y', strtotime($p['created_at'])) : '—' ?></td>
                            <?php if ($usuario['rol'] === 'admin'): ?>
                                <td><?= htmlspecialchars($p['creador_nombre'] ?? 'Desconocido') ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="#" class="editar-btn" data-id="<?= $p['id'] ?>">Editar</a> |
                                <a href="controllers/ProjectController.php?action=delete&id=<?= $p['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este proyecto?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes proyectos aún.</p>
        <?php endif; ?>

        <br>
        <a href="vistaCrearProyectos.php" class="button-link">+ Nuevo Proyecto</a>
        <a href="javascript:history.back()" class="button-link">Volver</a>
    </main>
</div>

<!-- Modal para editar proyectos -->
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

            fetch("editarProyectos.php?id=" + id)
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

function cerrarModal(id) {
    document.getElementById(id).style.display = "none";
}
</script>

</body>
</html>
