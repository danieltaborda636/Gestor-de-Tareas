<?php
require_once __DIR__ . "/../models/Task.php"; // Incluye el modelo Task

// Comprueba si se pasó el parámetro 'action' por GET
if (isset($_GET['action'])) {
    $action = $_GET['action']; // Acción a realizar: create, update, delete

    switch ($action) {
        case "create":
            // Si la acción es 'create' se espera el metodo POST con 'title'
            if (!empty($_POST['title'])) {
                Task::create($_POST['title'], $_POST['description'], 1, $_POST['priority'], $_POST['status']); // Llama a Task::create con los datos recibidos (creator_id=1 por simplicidad)
            }

            header("Location: ../views/tasks/list.php"); // Redirige al listado
            break;

        case "update":
            // Si la acción es 'update' se espera el metodo POST con 'id'
            if (!empty($_POST['id'])) {
                Task::update($_POST['id'], $_POST['title'], $_POST['description'], $_POST['priority'], $_POST['status']); // Llama a Task::update con los datos recibidos
            }
            
            header("Location: ../views/tasks/list.php"); // Redirige al listado
            break;

        case "delete":
            // Para eliminar se espera un id en GET (ej: ?action=delete&id=3)
            if (isset($_GET['id'])) {
                Task::delete($_GET['id']); // Llama al método delete
            }
            
            header("Location: ../views/tasks/list.php"); // Redirige al listado
            break;
    }
}

?>