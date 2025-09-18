<?php
// controllers/AuthController.php
// Controlador para manejar registro, login, sesión y "recordarme" con tokens (PDO).

session_start(); // Necesario para $_SESSION

// --- Dependencias ---
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// --- Conexión a BD ---
$database = new Databasee();
$db = $database->getConnection(); // devuelve PDO
$userModel = new User($db);

// --- Funciones auxiliares ---
function redirect_home() {
    header('Location: ../inicio.php');
    exit;
}
function redirect_login() {
    header('Location: ../login.php');
    exit;
}
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// --- Determinar acción ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

// =====================================================
// --- REGISTRO DE USUARIO ---
// =====================================================
if ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    $nombre   = isset($_POST['userName']) ? trim($_POST['userName']) : '';
    $correo   = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
    $password = isset($_POST['userPassword']) ? $_POST['userPassword'] : '';

    // Validaciones
    if ($nombre === '' || $correo === '' || $password === '') {
        set_flash('error', 'Por favor completa todos los campos.');
        redirect_login();
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'El correo no tiene un formato válido.');
        redirect_login();
    }
    if (strlen($password) < 6) {
        set_flash('error', 'La contraseña debe tener al menos 6 caracteres.');
        redirect_login();
    }

    // Verificar si el correo ya existe
    $existing = $userModel->findByEmail($correo);
    if ($existing) {
        set_flash('error', 'La cuenta ya existe (correo registrado).');
        redirect_login();
    }

    // Hashear contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Crear usuario
    $createResult = $userModel->create($nombre, $correo, $hash);

    if ($createResult) {
        // Obtener ID del usuario creado
        $newUserId = $db->lastInsertId();

        session_regenerate_id(true); // Seguridad
        $_SESSION['usuario_id'] = $newUserId;
        $_SESSION['user'] = [
            'id'          => $newUserId,
            'nombre'      => $nombre,
            'correo'      => $correo,
            'foto_perfil' => 'assets/uploads/default.jpeg'
        ];

        set_flash('success', 'Usuario registrado correctamente. ¡Bienvenido!');
        redirect_home();
    } else {
        set_flash('error', 'Ocurrió un error al registrar el usuario.');
        redirect_login();
    }
}

// =====================================================
// --- LOGIN ---
// =====================================================
elseif ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    $correo   = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
    $password = isset($_POST['userPassword']) ? $_POST['userPassword'] : '';

    if ($correo === '' || $password === '') {
        set_flash('error', 'Por favor completa todos los campos.');
        redirect_login();
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'El correo no tiene un formato válido.');
        redirect_login();
    }

    // Verificar credenciales
    $user = $userModel->verifyCredentials($correo, $password);

    if ($user === false) {
        $exists = $userModel->findByEmail($correo);
        if (!$exists) {
            set_flash('error', 'El correo no está registrado.');
        } else {
            set_flash('error', 'Contraseña incorrecta.');
        }
        redirect_login();
    } else {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['user'] = [
            'id'          => $user['id'],
            'nombre'      => $user['nombre_usuario'],
            'correo'      => $user['correo'],
            'foto_perfil' => !empty($user['foto_perfil']) ? $user['foto_perfil'] : 'assets/uploads/default.jpeg'
        ];

        // =========================================
        // --- "Recordarme" con token ---
        // =========================================
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
            $token = bin2hex(random_bytes(32));

            $sql ="INSERT INTO tokens (user_id, token, expiracion, creado_en) VALUES (:usuario_id, :token, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ":usuario_id" => $user['id'],
                ":token"      => $token
            ]);

            setcookie('remember_me', $token, [
                'expires'  => time() + 60*60*24*30, // 30 días
                'path'     => '/',
                'secure'   => false,  // cámbialo a true si usas HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        set_flash('success', 'Sesión iniciada correctamente.');
        redirect_home();
    }
}

// =====================================================
// --- SI NO HAY ACCIÓN ---
// =====================================================
else {
    redirect_home();
}