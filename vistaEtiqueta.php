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
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/vistaEtiqueta.css">
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
                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Avatar de Usuario">
                    <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                    <form action="logout.php" method="POST">
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
    </main>
</div>
</body>
</html>
