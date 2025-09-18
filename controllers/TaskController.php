<?php
// controllers/TaskController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Etiqueta.php';

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Conexión PDO
$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);
$etiquetaModel = new Etiqueta(); // ya conecta internamente

// Acción recibida por GET
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case "create":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
            $data = [
                "title"          => $_POST['title'],
                "description"    => $_POST['description'] ?? '',
                "creator_id"     => $_SESSION['user']['id'], // usuario logueado
                "assignee_id"    => $_POST['assignee_id'] ?? null,
                "project_id"     => $_POST['project_id'] ?? null,
                "parent_task_id" => $_POST['parent_task_id'] ?? null,
                "status"         => $_POST['status'] ?? 'todo',
                "priority"       => $_POST['priority'] ?? 'medium',
                "start_date"     => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                "due_date"       => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                "recurrence_rule"=> $_POST['recurrence_rule'] ?? 'none',
                "position"       => $_POST['position'] ?? 0
            ];

            if ($taskModel->create($data)) {
                $task_id = $db->lastInsertId();

                // Guardar etiquetas (si se mandaron)
                $labels = $_POST['labels'] ?? [];
                if (!empty($labels)) {
                    $etiquetaModel->setForTask($task_id, $labels);
                }

                $_SESSION['mensaje'] = "✅ Tarea creada con éxito";
            } else {
                $_SESSION['error'] = "❌ Error al crear la tarea";
            }
        }
        header("Location: ../views/tasks/list.php");
        exit();

    case "update":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
            $data = [
                "title"          => $_POST['title'],
                "description"    => $_POST['description'] ?? '',
                "assignee_id"    => $_POST['assignee_id'] ?? null,
                "project_id"     => $_POST['project_id'] ?? null,
                "parent_task_id" => $_POST['parent_task_id'] ?? null,
                "status"         => $_POST['status'],
                "priority"       => $_POST['priority'],
                "start_date"     => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                "due_date"       => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                "recurrence_rule"=> $_POST['recurrence_rule'] ?? 'none',
                "position"       => $_POST['position'] ?? 0
            ];

            if ($taskModel->update($_POST['id'], $data)) {
                // Guardar etiquetas (si se mandaron)
                $labels = $_POST['labels'] ?? [];
                $etiquetaModel->setForTask($_POST['id'], $labels);

                $_SESSION['mensaje'] = "✅ Tarea actualizada con éxito";
            } else {
                $_SESSION['error'] = "❌ Error al actualizar la tarea";
            }
        }
        header("Location: ../views/tasks/list.php");
        exit();

    case "delete":
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if ($taskModel->delete($_GET['id'])) {
                $_SESSION['mensaje'] = "✅ Tarea eliminada con éxito";
            } else {
                $_SESSION['error'] = "❌ Error al eliminar la tarea";
            }
        }
        header("Location: ../views/tasks/list.php");
        exit();

    default:
        header("Location: ../views/tasks/list.php");
        exit();
}
