<?php
session_start();
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../models/User.php"; // Asegúrate de tener cargado el modelo User

if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    // ✅ Corregido: era Databasee, debe ser Database
    $database = new Database();
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
                // ✅ Ruta corregida para que siempre cargue la foto o un default
                'foto_perfil' => !empty($userData['foto_perfil']) 
                    ? '/proyecto-1/Gestor-de-Tareas/assets/uploads/' . $userData['foto_perfil'] 
                    : '/proyecto-1/Gestor-de-Tareas/assets/uploads/default.jpeg'
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header class="top-bar">
        <div class="search-box">
            <input type="text" placeholder="Buscar...">
            <i class="icon-search"></i>
        </div>
        <div class="user-actions">
            <i class="icon-bell"></i>
            <i class="icon-settings"></i>
            <div class="user-profile">
                <!-- ✅ Ahora mostrará la foto real o el default -->
                <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                <i class="icon-dropdown"></i>
            </div>
        </div>
    </header>
</body>
</html>
