<?php
// logout.php
// Cierra la sesión del usuario de forma segura, borra token "remember_me" y redirige al login.

session_start();

// --- Si existe cookie "remember_me", eliminarla de la BD ---
if (isset($_COOKIE['remember_me'])) {
    require_once __DIR__ . '/config/Database.php';
    $database = new Databasee();
    $db = $database->getConnection();

    $token = $_COOKIE['remember_me'];

    // Borrar token de la tabla sesiones
    $sql = "DELETE FROM tokens WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->execute([":token" => $token]);

    // Borrar cookie en el navegador
    setcookie("remember_me", "", time() - 3600, "/");
}

// --- Eliminar variables de sesión ---
$_SESSION = [];

// --- Borrar cookie de sesión (PHPSESSID) ---
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// --- Destruir la sesión ---
session_destroy();

// --- Redirigir al login ---
header("Location: login.php");
exit;
