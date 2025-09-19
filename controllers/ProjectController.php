<?php
require_once __DIR__ . "/../models/Project.php"; // Incluye el modelo Project

// Comprueba si se pasó el parámetro 'action' por GET
if (isset($_GET['action'])) {
    $action = $_GET['action']; // Acción a realizar: create, update, delete

    switch ($action) {
        case "create":
            // Si la acción es 'create' se espera el metodo POST con 'name'
            if (!empty($_POST['name'])) {
                Projects::create($_POST['name'], $_POST['description'], 1); // Llama a Project::create con los datos recibidos (owner_id=1 por simplicidad)
            }

            header("Location: ../vistaProyectos.php"); // Redirige al listado
            break;

        case "update":
            // Si la acción es 'update' se espera el metodo POST con 'id'
            if (!empty($_POST['id'])) {
                Projects::update($_POST['id'], $_POST['name'], $_POST['description']); // Llama a Project::update con los datos recibidos
            }
            
            header("Location: ../vistaProyectos.php"); // Redirige al listado
            break;

        case "delete":
            // Para eliminar se espera un id en GET (ej: ?action=delete&id=3)
            if (isset($_GET['id'])) {
                Projects::delete($_GET['id']); // Llama al método delete
            }
            
            header("Location: ../vistaProyectos.php"); // Redirige al listado
            break;
    }
}

?>