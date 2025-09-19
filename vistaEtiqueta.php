<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Etiqueta.php';

// =============================
// 1) Autenticación
// =============================
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $database = new Databasee();
    $db = $database->getConnection();

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

// =============================
// 2) Obtener etiquetas con modelo
// =============================
$database = new Databasee();
$db = $database->getConnection();
$etiquetaModel = new Etiqueta($db);
$etiquetas = $etiquetaModel->all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/vistaEtiqueta.css">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <i class="icon-settings"></i>
                <div class="user-profile">
                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Avatar de Usuario">
                    <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                    <form action="logoutphp." method="POST">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Botón para abrir el modal -->
        <button type="button" onclick="abrirModal()">Crear Etiqueta</button>

        <!-- Modal oculto -->
        <div id="modalEtiqueta" class="modal">
            <div class="modal-contenido">
                <span class="cerrar" onclick="cerrarModal()">&times;</span>
                <h2>Crear Etiqueta</h2>
                <form action="./views/etiquetas/CrearEtiqueta.php" method="POST">
                    <div>
                        <label for="nombre">Nombre de la etiqueta:</label>
                        <input type="text" id="nombre" name="nombre_etiqueta" required>
                    </div>
                    
                    <div>
                        <label for="color">Color de la etiqueta:</label>
                        <input type="color" id="color" name="color" value="#000000" required>
                    </div>

                    <button type="submit">Crear Etiqueta</button>
                </form>
            </div>
        </div>
        <script>
            function abrirModal() {
                document.getElementById("modalEtiqueta").style.display = "flex";
            }
            function cerrarModal() {
                document.getElementById("modalEtiqueta").style.display = "none";
            }
        </script>

        <ul>
            <?php if (!empty($etiquetas)): ?>
                <?php foreach ($etiquetas as $etiqueta): ?>
                    <li>
                        <?= htmlspecialchars($etiqueta["nombre_etiqueta"]) ?>
                        <span><i class="fas fa-circle" style="color:<?= htmlspecialchars($etiqueta["color"]) ?>;"></i></span>
                        <form action="./views/etiquetas/eliminarEtiqueta.php" method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($etiqueta["id"]) ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </li>
                <?php endforeach ?>
            <?php else: ?>
                <p>No hay etiquetas registradas.</p>
            <?php endif ?>
        </ul>
        <a href="javascript:history.back()" class="button-link">Volver</a>
    </main>
</div>
</body>
</html>
