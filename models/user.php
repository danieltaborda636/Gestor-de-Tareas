<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    private $db; // Guardará la conexión mysqli

    // ------------------------
    // Constructor
    // ------------------------
    public function __construct() {
        // Creamos instancia de Database y obtenemos conexión
        $database = new Databasee();
        $this->db = $database->getConnection();
    }

    // ------------------------
    // Buscar usuario por correo
    // ------------------------
    public function findByEmail($correo) {
        $stmt = $this->db->prepare(
            "SELECT id, nombre_usuario, correo, contrasena, foto_perfil, fecha_registro 
             FROM usuarios 
             WHERE correo = ? LIMIT 1"
        );

        if (!$stmt) return null;

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    // ------------------------
    // Buscar usuario por ID o correo
    // ------------------------
    public function findByEmailOrId($value) {
        if (is_numeric($value)) {
            // Buscar por ID
            $stmt = $this->db->prepare(
                "SELECT id, nombre_usuario, correo, contrasena, foto_perfil, fecha_registro 
                 FROM usuarios 
                 WHERE id = ? LIMIT 1"
            );
            if (!$stmt) return null;
            $stmt->bind_param("i", $value);
        } else {
            // Buscar por correo
            $stmt = $this->db->prepare(
                "SELECT id, nombre_usuario, correo, contrasena, foto_perfil, fecha_registro 
                 FROM usuarios 
                 WHERE correo = ? LIMIT 1"
            );
            if (!$stmt) return null;
            $stmt->bind_param("s", $value);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    // ------------------------
    // Crear un nuevo usuario con foto por defecto
    // ------------------------
    public function create($nombre, $correo, $contrasenaHash) {
        $foto_default = 'assets/uploads/default.jpeg'; // Ruta de la foto por defecto

        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre_usuario, correo, contrasena, foto_perfil) 
             VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) return false;

        $stmt->bind_param("ssss", $nombre, $correo, $contrasenaHash, $foto_default);
        $ok = $stmt->execute();

        if (!$ok) {
            $stmt->close();
            return false;
        }

        $insertId = $stmt->insert_id;
        $stmt->close();

        return $insertId;
    }

    // ------------------------
    // Verificar credenciales (correo + contraseña)
    // ------------------------
    public function verifyCredentials($correo, $password) {
        $user = $this->findByEmail($correo);

        if (!$user) return false;

        // Verificamos la contraseña con el hash guardado
        if (password_verify($password, $user['contrasena'])) {
            return $user;
        }

        return false;
    }
}
