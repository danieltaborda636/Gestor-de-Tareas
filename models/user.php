<?php
// models/User.php
// Modelo para la tabla "usuarios" usando PDO

class User {
    private $conn;
    private $table = "usuarios";

    public function __construct($db) {
        $this->conn = $db; // $db es un objeto PDO
    }

    // ============================
    // Crear nuevo usuario
    // ============================
    public function create($nombre, $correo, $passwordHash, $rol = 'user') {
        $sql = "INSERT INTO {$this->table} 
                (nombre_usuario, correo, contrasena, foto_perfil, rol) 
                VALUES (:nombre, :correo, :contrasena, :foto, :rol)";
        $stmt = $this->conn->prepare($sql);

        $fotoDefecto = "assets/uploads/default.jpeg";

        return $stmt->execute([
            ":nombre" => $nombre,
            ":correo" => $correo,
            ":contrasena" => $passwordHash,
            ":foto" => $fotoDefecto,
            ":rol" => $rol
        ]);
    }

    // ============================
    // Buscar usuario por correo
    // ============================
    public function findByEmail($correo) {
        $sql = "SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":correo" => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================
    // Buscar usuario por ID
    // ============================
    public function getUserById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================
    // Verificar credenciales (login)
    // ============================
    public function verifyCredentials($correo, $password) {
        $user = $this->findByEmail($correo);
        if ($user && password_verify($password, $user['contrasena'])) {
            return $user;
        }
        return false;
    }

    // ============================
    // Actualizar usuario
    // ============================
    public function updateUser($id, $nombre, $correo, $contrasena = null, $fotoPerfil = null, $rol = null) {
        $sql = "UPDATE {$this->table} SET nombre_usuario = :nombre, correo = :correo";
        $params = [
            ":nombre" => $nombre,
            ":correo" => $correo,
            ":id" => $id
        ];

        if (!empty($contrasena)) {
            $sql .= ", contrasena = :contrasena";
            $params[":contrasena"] = password_hash($contrasena, PASSWORD_BCRYPT);
        }

        if (!empty($fotoPerfil)) {
            $sql .= ", foto_perfil = :foto";
            $params[":foto"] = $fotoPerfil;
        }

        if (!empty($rol)) {
            $sql .= ", rol = :rol";
            $params[":rol"] = $rol;
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // ============================
    // Listar todos los usuarios
    // ============================
    public function getAllUsers() {
        $sql = "SELECT * FROM {$this->table} ORDER BY fecha_registro DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // Eliminar usuario
    // ============================
    public function deleteUser($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }
}