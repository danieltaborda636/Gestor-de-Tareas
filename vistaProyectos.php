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
$proyectos = Projects::all();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Proyectos</title>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['description']) ?></td>
                            <td><?= !empty($p['created_at']) ? date('d-m-Y', strtotime($p['created_at'])) : '—' ?></td>
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
