<?php
require_once __DIR__ . "/../models/Project.php"; 

session_start();

// Validar que el usuario esté logueado
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?error=Debes iniciar sesión");
    exit;
}

$usuario = $_SESSION['user']; // Contiene id, nombre, rol, etc.

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case "create":
            if (!empty($_POST['name'])) {
                $name        = trim($_POST['name']);
                $description = trim($_POST['description']);
                $owner_id    = $usuario['id']; // 👈 guarda el dueño real

                Projects::create($name, $description, $owner_id);
            }
            header("Location: ../vistaProyectos.php");
            break;

        case "update":
            if (!empty($_POST['id'])) {
                $id          = (int)$_POST['id'];
                $name        = trim($_POST['name']);
                $description = trim($_POST['description']);

                Projects::update($id, $name, $description);
            }
            header("Location: ../vistaProyectos.php");
            break;

        case "delete":
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                Projects::delete($id);
            }
            header("Location: ../vistaProyectos.php");
            break;
    }
}
?>