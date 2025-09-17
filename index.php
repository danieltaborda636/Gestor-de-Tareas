<?php
// index.php (perfil)

// -----------------
// 1) INICIAMOS SESIÓN
// -----------------
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';

// -----------------
// 2) GESTIÓN DE "REMEMBER ME" AUTOMÁTICO
// -----------------
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

// -----------------
// 3) VERIFICAMOS SESIÓN ACTIVA
// -----------------
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// -----------------
// 4) OBTENEMOS DATOS DEL USUARIO
// -----------------
$usuario = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="css/perfil.css"> <!-- Tu CSS -->
</head>
<body>
    <!-- Sidebar (40%) -->
    <div class="sidebar">
        <div class="user-info">
            <!-- Foto -->
            <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil">
            <!-- Nombre y correo -->
            <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
            <p><?php echo htmlspecialchars($usuario['correo']); ?></p>
        </div>

        <!-- Botones de navegación -->
        <div class="nav-buttons">
            <button onclick="mostrarFormulario()">Editar perfil</button>
            <a href="tareas.php"><button type="button">Ver tareas</button></a>
            <a href="etiquetas.php"><button type="button">Ver etiquetas</button></a>
            <a href="subtareas.php"><button type="button">Ver subtareas</button></a>
            <a href="configuracion.php"><button type="button">Configuración</button></a>
        </div>

        <!-- Logout -->
        <div class="logout">
            <form action="logout.php" method="POST">
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>

    <!-- Contenido (60%) -->
    <div class="content">
        <h1>Mi Perfil</h1>

        <!-- Mensajes flash -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert <?= $_SESSION['flash']['type'] ?>">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Formulario oculto (modificar perfil) -->
        <form class="form-modificar" id="formModificar" 
              method="POST" action="controllers/UserController.php?action=update" 
              enctype="multipart/form-data" style="display:none;">
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

    <script>
        // Mostrar/ocultar formulario
        function mostrarFormulario() {
            const form = document.getElementById("formModificar");
            form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
        }
    </script>
</body>
</html>
