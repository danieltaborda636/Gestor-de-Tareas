<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/models/User.php";

$database = new Databasee();
$db = $database->getConnection();

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
    <title>Nuevo Proyecto</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/vistaTareas.css">
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
                <input type="text" placeholder="Buscar...">
                <i class="icon-search"></i>
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

        <h2>Crear Proyecto</h2>

        <form method="POST" action="controllers/ProjectController.php?action=create">
            <label>Nombre:</label><br>
            <input type="text" name="name" placeholder="Nombre del proyecto" required><br><br>

            <label>Descripción:</label><br>
            <textarea name="description" placeholder="Descripción"></textarea><br><br>

            <button type="submit">Guardar</button>
        </form>

        <br>
        <a href="javascript:history.back()" class="button-link">Volver</a>
    </main>
</div>
</body>
</html>
