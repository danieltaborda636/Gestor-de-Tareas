<?php
session_start();
require_once __DIR__ . '/config/Database.php';

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

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$usuario = $_SESSION['user'];
?>
<!-- se va hacer la senyencia para llamar las y poder visualizar las etiquetas -->
<?php

$conn = Database::connect();

$sentencia = $conn->prepare("SELECT * FROM etiquetas");
$sentencia->execute();
$result = $sentencia->get_result();

$etiquetas = $result->fetch_all(MYSQLI_ASSOC);



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
                <img src="http://localhost/proyecto-1/Gestor-de-Tareas/assets/uploads/logo.png" alt="Logo de Taskify"> <h1>TASKIFY</h1>
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
                        <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Avatar de Usuario">
                         <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        <form action="logout.php" method="POST">
                        <button type="submit">Cerrar sesión</button>
                        </form>
                        
                       
                    </div>
                </div>
            </header>
    <!-- Botón para abrir el modal -->
<button type="button" onclick="abrirModal()">Crear Etiqueta</button>

<!-- Modal oculto por defecto -->
<div id="modalEtiqueta" class="modal">
  <div class="modal-contenido">
    <span class="cerrar" onclick="cerrarModal()">&times;</span>
    <h2>Crear Etiqueta</h2>
    <form action="./views/Etiquetas/CrearEtiqueta.php" method="POST">
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
            <?php  foreach($etiquetas as $etiqueta): ?>
                    <li>
                        <?= $etiqueta["nombre_etiqueta"] ?>
                        <span><i class="fas fa-circle" style="color:<?= $etiqueta["color"] ?>;"></i></span>
                        <form action="./views/etiquetas/eliminarEtiqueta.php" method = "POST">
                            <input type="hidden" name="id" value="<?= $etiqueta["id"] ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </li>
            <?php endforeach ?>
        <?php endif ?>
    </ul>



  
    </div>
</body>
</html>