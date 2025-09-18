<?php
// controllers/AuthController.php
// Controlador para manejar registro, login, sesión y "recordarme" con tokens.

// --- 1) INICIAMOS LA SESIÓN ---
session_start(); // Necesario para usar $_SESSION y mensajes flash

// --- 2) INCLUIMOS DEPENDENCIAS ---
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

// --- 3) CREAMOS LA CONEXIÓN Y EL MODELO ---
$database = new Databasee();       // Instanciamos Database
$db = $database->getConnection(); // Obtenemos conexión mysqli

try {
    $userModel = new User($db);  // Intentamos pasar la conexión al modelo
} catch (ArgumentCountError $e) {
    $userModel = new User();     // Si el constructor no acepta parámetros
}

// --- 4) LECTURA DEL ACTION ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 5) FUNCIONES AUXILIARES ---
// Redirige a la home
function redirect_home() {
    header('Location: ../inicio.php');
    exit;
}

// Redirige al login
function redirect_login() {
    header('Location: ../login.php');
    exit;
}

// Establece un mensaje flash
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// =====================================================
// --- 6) GESTIÓN DEL REGISTRO DE USUARIO ---
// =====================================================
if ($action === 'register') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    // Recogemos datos del formulario
    $nombre = isset($_POST['userName']) ? trim($_POST['userName']) : '';
    $correo = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
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

    // Comprobamos si el usuario ya existe
    $existing = $userModel->findByEmail($correo);
    if ($existing) {
        set_flash('error', 'La cuenta ya existe (correo registrado).');
        redirect_login();
    }

    // Hasheamos la contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Creamos el usuario
    $createResult = $userModel->create($nombre, $correo, $hash);

    if ($createResult) {
        $newUserId = is_int($createResult) ? $createResult : (int)$db->insert_id;
        session_regenerate_id(true); // Seguridad

        // Guardamos sesión del usuario con foto por defecto
        $_SESSION['usuario_id'] = $newUserId;
        $_SESSION['user'] = [
            'id' => $newUserId,
            'nombre' => $nombre,
            'correo' => $correo,
            'foto_perfil' => 'assets/uploads/default.jpeg' // Foto por defecto
        ];

        set_flash('success', 'Usuario registrado y autenticado correctamente. ¡Bienvenido!');
        redirect_home();
    } else {
        set_flash('error', 'Ocurrió un error al registrar el usuario. Intenta de nuevo.');
        redirect_login();
    }
}

// =====================================================
// --- 7) GESTIÓN DEL LOGIN ---
// =====================================================
elseif ($action === 'login') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_login();

    $correo = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
    $password = isset($_POST['userPassword']) ? $_POST['userPassword'] : '';

    // Validaciones rápidas
    if ($correo === '' || $password === '') {
        set_flash('error', 'Por favor completa todos los campos.');
        redirect_login();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'El correo no tiene un formato válido.');
        redirect_login();
    }

    // Verificamos credenciales
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
        session_regenerate_id(true); // Seguridad

        // Guardamos sesión del usuario
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nombre' => isset($user['nombre_usuario']) ? $user['nombre_usuario'] : ($user['nombre'] ?? ''),
            'correo' => $user['correo'],
            'foto_perfil' => !empty($user['foto_perfil']) ? $user['foto_perfil'] : 'assets/uploads/default.jpeg'
        ];

        // =================================================
        // --- 8) IMPLEMENTACIÓN DEL "RECORDARME" TOKEN ---
        // =================================================
        if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
            // Generar token seguro
            $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales

            // Guardar token en la tabla sesiones
            $stmt = $db->prepare("INSERT INTO sesiones (usuario_id, token, creado_en) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user['id'], $token);
            $stmt->execute();
            $stmt->close();

            // Guardar token en cookie segura
            setcookie('remember_me', $token, [
                'expires' => time() + 60*60*24*30, // 30 días
                'path' => '/',
                'secure' => true,     // HTTPS obligatorio (recomendado)
                'httponly' => true,   // No accesible desde JS
                'samesite' => 'Strict' // Evita CSRF
            ]);
        }

       
        redirect_home();
    }
}

// =====================================================
// --- 9) ACCIÓN NO RECONOCIDA ---
// =====================================================
else {
    redirect_home();
}
