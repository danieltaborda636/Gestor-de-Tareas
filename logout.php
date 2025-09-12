<?php
// logout.php
// Este archivo cierra la sesión del usuario de forma segura y lo redirige al login.

// --- 1) Iniciamos la sesión ---
// Necesario para acceder a $_SESSION y poder destruirla.
session_start();

// --- 2) Eliminamos todas las variables de sesión ---
// Esto limpia la variable global $_SESSION.
$_SESSION = [];

// --- 3) Borramos la cookie de sesión (si existe) ---
// Esto asegura que el navegador elimine el identificador de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),     // nombre de la cookie
        '',                 // valor vacío
        time() - 42000,     // fecha en el pasado => expira
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// --- 4) Finalmente destruimos la sesión ---
// Esto elimina completamente la sesión en el servidor.
session_destroy();

// --- 5) Redirigimos al login ---
// Cambia "login.php" por la ruta real de tu archivo de login.
header("Location: login.php");
exit;
