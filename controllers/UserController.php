<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$database = new Databasee();
$db = $database->getConnection();
$userModel = new User($db);

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    // =====================================================
    // CREAR USUARIO (solo admin)
    // =====================================================
    case 'create':
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            header("Location: ../inicio.php");
            exit();
        }

        $nombre = $_POST['nombre_usuario'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = $_POST['rol'] ?? 'user';

        if ($nombre && $correo && $contrasena) {
            $userModel->create($nombre, $correo, $contrasena, $rol);
        }

        header("Location: ../vistaUsuarios.php");
        exit();

    // =====================================================
    // EDITAR USUARIO (admin o el propio usuario)
    // =====================================================
    case 'update':
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header("Location: ../inicio.php");
            exit();
        }

        // Validar permisos (admin o dueño de la cuenta)
        if ($_SESSION['user']['rol'] !== 'admin' && $_SESSION['user']['id'] != $id) {
            header("Location: ../inicio.php");
            exit();
        }

        $nombre = $_POST['nombre_usuario'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = $_POST['rol'] ?? null;

        // Foto de perfil
        $fotoPerfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $directorio = __DIR__ . '/../assets/uploads/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            $nombreArchivo = uniqid() . "_" . basename($_FILES['foto_perfil']['name']);
            $rutaArchivo = $directorio . $nombreArchivo;

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaArchivo)) {
                $fotoPerfil = "assets/uploads/" . $nombreArchivo;
            }
        }

        $userModel->updateUser($id, $nombre, $correo, $contrasena, $rol, $fotoPerfil);

        // Si el usuario actualizó su propio perfil, refrescar sesión
        if ($_SESSION['user']['id'] == $id) {
            $_SESSION['user']['nombre'] = $nombre;
            $_SESSION['user']['correo'] = $correo;
            if ($fotoPerfil) {
                $_SESSION['user']['foto_perfil'] = $fotoPerfil;
            }
        }

        if ($_SESSION['user']['rol'] === 'admin') {
            header("Location: ../vistaUsuarios.php");
        } else {
            header("Location: ../inicio.php");
        }
        exit();

    // =====================================================
    // ELIMINAR USUARIO (solo admin)
    // =====================================================
    case 'delete':
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            header("Location: ../inicio.php");
            exit();
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $userModel->deleteUser($id);
        }

        header("Location: ../vistaUsuarios.php");
        exit();

    // =====================================================
    // SI NO HAY ACCIÓN
    // =====================================================
    default:
        header("Location: ../inicio.php");
        exit();
}