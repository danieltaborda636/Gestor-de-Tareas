<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Comment.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$database = new Databasee();
$db = $database->getConnection();
$commentModel = new Comment($db);

$action = $_GET['action'] ?? '';
$taskId = $_POST['task_id'] ?? $_GET['task_id'] ?? null;

switch ($action) {
    case "create":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['body'])) {
            $commentModel->create($taskId, $_SESSION['user']['id'], $_POST['body']);
        }
        break;

    case "update":
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id']) && !empty($_POST['body'])) {
            $commentModel->update($_POST['id'], $_POST['body']);
        }
        break;

    case "delete":
        if (isset($_GET['id'])) {
            $commentModel->delete($_GET['id']);
        }
        break;
}

header("Location: ../verTarea.php?id=$taskId");
exit();
?>