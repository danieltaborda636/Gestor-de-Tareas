<?php
// controllers/TaskController.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Etiqueta.php';
require_once __DIR__ . '/../models/Historial.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$database = new Databasee();
$db = $database->getConnection();
$taskModel = new Task($db);
$etiquetaModel = new Etiqueta();
$historialModel = new Historial($db);

$action = $_GET['action'] ?? '';

switch ($action) {

    case "create":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
            $parent_id = $_POST['parent_task_id'] ?? null;

            // 🚫 Validar que no se creen subtareas de subtareas
            if ($parent_id) {
                $parentTask = $taskModel->find($parent_id);
                if ($parentTask && $parentTask['parent_task_id']) {
                    $_SESSION['error'] = "No se pueden crear subtareas de subtareas.";
                    header("Location: ../verTarea.php?id=" . $parentTask['parent_task_id']);
                    exit();
                }
            }

            $data = [
                "title"          => $_POST['title'],
                "description"    => $_POST['description'] ?? '',
                "creator_id"     => $_SESSION['user']['id'],
                // 👇 solo admin puede asignar
                "assignee_id"    => ($_SESSION['user']['rol'] === 'admin') ? ($_POST['assignee_id'] ?? null) : null,
                "project_id"     => $_POST['project_id'] ?? null,
                "parent_task_id" => $parent_id,
                "status"         => $_POST['status'] ?? 'todo',
                "priority"       => $_POST['priority'] ?? 'medium',
                "start_date"     => $_POST['start_date'] ?: null,
                "due_date"       => $_POST['due_date'] ?: null,
                "recurrence_rule"=> $_POST['recurrence_rule'] ?? 'none',
                "position"       => $_POST['position'] ?? 0
            ];

            try {
                if ($taskModel->create($data)) {
                    $task_id = $db->lastInsertId();

                    $labels = $_POST['labels'] ?? [];
                    if (!empty($labels)) {
                        $etiquetaModel->setForTask($task_id, $labels);
                    }

                    $historialModel->registrar($_SESSION['user']['id'], 'create', "Creó la tarea ID $task_id");
                    $_SESSION['mensaje'] = "Tarea creada con éxito";

                    if (!empty($data['parent_task_id'])) {
                        header("Location: ../verTarea.php?id=" . $data['parent_task_id']);
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Error al crear la tarea";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../vistaTareas.php");
                exit();
            }
        }
        header("Location: ../vistaTareas.php");
        exit();

    case "update":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
            $task = $taskModel->find($_POST['id']);

            // 🚫 Validar permisos (miembro solo puede editar si es creador o asignado)
            if ($_SESSION['user']['rol'] !== 'admin' && $task['creator_id'] != $_SESSION['user']['id'] && $task['assignee_id'] != $_SESSION['user']['id']) {
                $_SESSION['error'] = "No tienes permiso para editar esta tarea.";
                header("Location: ../vistaTareas.php");
                exit();
            }

            $data = [
                "title"          => $_POST['title'],
                "description"    => $_POST['description'] ?? '',
                // 👇 solo admin puede cambiar asignación
                "assignee_id"    => ($_SESSION['user']['rol'] === 'admin') ? ($_POST['assignee_id'] ?? null) : $task['assignee_id'],
                "project_id"     => $_POST['project_id'] ?? null,
                "parent_task_id" => $_POST['parent_task_id'] ?? null,
                "status"         => $_POST['status'],
                "priority"       => $_POST['priority'],
                "start_date"     => $_POST['start_date'] ?: null,
                "due_date"       => $_POST['due_date'] ?: null,
                "recurrence_rule"=> $_POST['recurrence_rule'] ?? 'none',
                "position"       => $_POST['position'] ?? 0
            ];

            if ($taskModel->update($_POST['id'], $data, $_SESSION['user']['id'])) {
                $labels = $_POST['labels'] ?? [];
                $etiquetaModel->setForTask($_POST['id'], $labels);

                $historialModel->registrar($_SESSION['user']['id'], 'update', "Actualizó la tarea ID {$_POST['id']}");
                $_SESSION['mensaje'] = "Tarea actualizada con éxito";

                if (!empty($data['parent_task_id'])) {
                    header("Location: ../verTarea.php?id=" . $data['parent_task_id']);
                    exit();
                }
            } else {
                $_SESSION['error'] = "Error al actualizar la tarea";
            }
        }
        header("Location: ../vistaTareas.php");
        exit();

    case "delete":
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $taskId = $_GET['id'];
            $task = $taskModel->find($taskId);

            // 🚫 Validar permisos: admin puede borrar todo, miembro solo lo que creó
            if ($_SESSION['user']['rol'] !== 'admin' && $task['creator_id'] != $_SESSION['user']['id']) {
                $_SESSION['error'] = "No tienes permiso para eliminar esta tarea.";
                header("Location: ../vistaTareas.php");
                exit();
            }

            if ($taskModel->delete($taskId, $_SESSION['user']['id'])) {
                $historialModel->registrar($_SESSION['user']['id'], 'delete', "Eliminó la tarea ID $taskId");
                $_SESSION['mensaje'] = "Tarea eliminada con éxito";

                if (!empty($task['parent_task_id'])) {
                    header("Location: ../verTarea.php?id=" . $task['parent_task_id']);
                    exit();
                }
            } else {
                $_SESSION['error'] = "Error al eliminar la tarea";
            }
        }
        header("Location: ../vistaTareas.php");
        exit();

    default:
        header("Location: ../vistaTareas.php");
        exit();
}