<?php
// Listado de tareas
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tareas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Listado de tareas</h2>
    <a href="create.php">Nueva tarea</a>
    <ul>
        <!-- Aquí se listarán las tareas desde TaskController -->
        <li>Tarea de ejemplo</li>
    </ul>
</body>
</html>
