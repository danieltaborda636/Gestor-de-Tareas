<?php
// index.php -> punto de entrada
session_start();

// Si no hay sesión, redirige al login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/auth/login.php");
    exit();
}

// Si hay sesión, mostramos la lista de tareas
header("Location: views/tasks/list.php");
exit();

?>