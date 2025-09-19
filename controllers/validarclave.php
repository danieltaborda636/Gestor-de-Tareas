<?php
// controllers/validarclave.php
session_start();
require_once("../config/database.php"); // Conexión PDO

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

// Validar campos
if (!isset($_POST['token']) || empty($_POST['token']) || !isset($_POST['contraseña'])) {
    $_SESSION['resultado'] = "Datos inválidos.";
    header("Location: ../recuperarcontraseña.php?token=" . urlencode($_POST['token'] ?? ''));
    exit();
}

$token = $_POST['token'];
$nuevaClave = $_POST['contraseña'];

try {
    $conn = Databasee::connect(); // PDO

    // 1️⃣ Verificar token válido
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM contrasenasrecuperar WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fila) {
        $_SESSION['resultado'] = "Token no válido.";
        header("Location: ../olvidemicontra.php");
        exit();
    }

    // 2️⃣ Verificar expiración
    if (new DateTime() > new DateTime($fila['expires_at'])) {
        $_SESSION['resultado'] = "";
        header("Location: ../olvidemicontra.php");
        exit();
    }

    $userId = $fila['user_id'];

    // 3️⃣ Obtener contraseña actual
    $stmt = $conn->prepare("SELECT contrasena FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['resultado'] = "Usuario no encontrado.";
        header("Location: ../olvidemicontra.php");
        exit();
    }

    // 4️⃣ Validar que la nueva contraseña no sea igual a la anterior
    if (password_verify($nuevaClave, $usuario['contrasena'])) {
        $_SESSION['resultado'] = "La nueva clave no puede ser igual a la anterior.";
        header("Location: ../recuperarcontraseña.php?token=" . urlencode($token));
        exit();
    }

    // 5️⃣ Actualizar contraseña
    $hash = password_hash($nuevaClave, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE usuarios SET contrasena = :contrasena WHERE id = :id");
    $stmt->execute([':contrasena' => $hash, ':id' => $userId]);

    // 6️⃣ Borrar token usado
    $stmt = $conn->prepare("DELETE FROM contrasenasrecuperar WHERE token = :token");
    $stmt->execute([':token' => $token]);

    $_SESSION['resultado'] = "Contraseña cambiada correctamente. Ahora puedes iniciar sesión.";
    header("Location: ../login.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['resultado'] = "Error en la base de datos: " . $e->getMessage();
    header("Location: ../recuperarcontraseña.php?token=" . urlencode($token));
    exit();
}
