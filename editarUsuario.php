<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '//models/User.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: ../../inicio.php");
    exit();
}

$database = new Databasee();
$db = $database->getConnection();
$userModel = new User($db);

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: ../../vistaUsuarios.php");
    exit();
}

$usuario = $userModel->getUserById($id);
if (!$usuario) {
    header("Location: ../../vistaUsuarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h1 class="mb-4">Editar Usuario</h1>

    <form action="./controllers/UserController.php?action=update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

        <div class="mb-3">
            <label class="form-label">Nombre de Usuario</label>
            <input type="text" name="nombre_usuario" class="form-control" 
                   value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" 
                   value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" name="contrasena" class="form-control" placeholder="(Opcional)">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="rol" class="form-select">
                <option value="user" <?= $usuario['rol'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Foto de Perfil</label><br>
            <img src="../../<?= htmlspecialchars($usuario['foto_perfil'] ?? 'assets/uploads/default.jpeg') ?>" 
                 alt="Foto de Perfil" width="100" class="mb-2 rounded-circle"><br>
            <input type="file" name="foto_perfil" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="./vistaUsuarios.php" class="btn btn-secondary">Cancelar</a>
    </form>
</body>
</html>
