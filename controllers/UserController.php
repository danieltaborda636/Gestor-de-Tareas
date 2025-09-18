<?php
// controllers/UserController.php
// Este controlador se encarga de actualizar los datos del usuario desde el perfil.

session_start();

// Incluimos la configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

// Incluimos el modelo User (asegúrate de que existe y que su clase se llama User)
require_once __DIR__ . '/../models/usermodelo.php';

class UserController {

    public function update() {
        // --- 1) Crear conexión con la BD ---
        $database = new Databasee();
        $db = $database->getConnection();

        // --- 2) Crear instancia del modelo ---
        $user = new User($db);

        // --- 3) Recibir datos del formulario ---
        $id = $_POST['id'];
        $nombre = $_POST['nombre_usuario'];
        $correo = $_POST['correo'];

        // Si la contraseña está vacía, mandamos null para no modificarla
        $contrasena = !empty($_POST['contrasena']) ? $_POST['contrasena'] : null;

        // --- 4) Manejo de la foto de perfil ---
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            // Carpeta donde guardaremos las fotos
            $carpeta = "../assets/uploads/";

            // Si no existe la carpeta, la creamos con permisos
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            // Generamos un nombre único para la foto
            $nombreArchivo = uniqid() . "_" . basename($_FILES['foto_perfil']['name']);
            $ruta = $carpeta . $nombreArchivo;

            // Movemos la foto desde el temporal a nuestra carpeta
            move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta);

            // Guardamos la ruta relativa (para usar en el src del <img>)
            $fotoPerfil = "assets/uploads/" . $nombreArchivo;
        } else {
            // Si no subió una nueva foto, dejamos la que ya tenía en la sesión
            $fotoPerfil = isset($_SESSION['user']['foto_perfil']) ? $_SESSION['user']['foto_perfil'] : "assets/default.png";
        }

        // --- 5) Guardar cambios en la BD ---
        $resultado = $user->updateUser($id, $nombre, $correo, $contrasena, $fotoPerfil);

        if ($resultado) {
            // --- 6) Actualizar también los datos en la sesión ---
            $_SESSION['user']['id'] = $id;
            $_SESSION['user']['nombre_usuario'] = $nombre;
            $_SESSION['user']['correo'] = $correo;
            $_SESSION['user']['foto_perfil'] = $fotoPerfil;

            // Mensaje de éxito
            $_SESSION['flash'] = ['type' => 'success', 'message' => '✅ Usuario actualizado correctamente'];
        } else {
            // Mensaje de error
            $_SESSION['flash'] = ['type' => 'error', 'message' => '❌ No se pudo actualizar el usuario'];
        }

        // --- 7) Redirigir de vuelta al perfil ---
        header("Location: ../inicio.php");
        exit();
    }
}

// --- 8) Ejecutamos la acción update cuando venga desde el formulario ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'update') {
    $controller = new UserController();
    $controller->update();
}
