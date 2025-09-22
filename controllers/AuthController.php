<?php
// controllers/AuthController.php
// Controlador para manejar registro, login, sesión y "recordarme" con tokens (PDO).

session_start();

// --- Dependencias ---
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// --- Conexión a BD ---
$database = new Databasee();
$db = $database->getConnection();
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

$action = $_GET['action'] ?? '';

// =====================================================
// --- REGISTRO DE USUARIO ---
// =====================================================
if ($action === 'register') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    $nombre   = trim($_POST['userName'] ?? '');
    $correo   = trim($_POST['userEmail'] ?? '');
    $password = $_POST['userPassword'] ?? '';

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
    if ($userModel->findByEmail($correo)) {
        set_flash('error', 'El correo ya está registrado.');
        redirect_login();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($userModel->create($nombre, $correo, $hash)) {
        $newUserId = $db->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $newUserId;
        $_SESSION['user'] = [
            'id'          => $newUserId,
            'nombre'      => $nombre,
            'correo'      => $correo,
            'foto_perfil' => 'assets/uploads/default.jpeg',
            'rol'         => 'miembro' // siempre al registrar
        ];

        set_flash('success', 'Usuario registrado correctamente.');
        redirect_home();
    } else {
        set_flash('error', 'Error al registrar el usuario.');
        redirect_login();
    }
}

// =====================================================
// --- LOGIN ---
// =====================================================
elseif ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    $correo   = trim($_POST['userEmail'] ?? '');
    $password = $_POST['userPassword'] ?? '';

    if ($correo === '' || $password === '') {
        set_flash('error', 'Completa todos los campos.');
        redirect_login();
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Correo inválido.');
        redirect_login();
    }

    $user = $userModel->verifyCredentials($correo, $password);

    if ($user === false) {
        set_flash('error', 'Credenciales incorrectas.');
        redirect_login();
    } else {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['user'] = [
            'id'          => $user['id'],
            'nombre'      => $user['nombre_usuario'],
            'correo'      => $user['correo'],
            'foto_perfil' => $user['foto_perfil'] ?? 'assets/uploads/default.jpeg',
            'rol'         => $user['rol'] // 👈 importante: guardar el rol en sesión
        ];

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