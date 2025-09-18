<?php
// models/User.php (antes usermodelo.php)
// Modelo que representa la tabla "usuarios" usando PDO

class User {
    private $conn;
    private $table = "usuarios";

    public function __construct($db) {
        $this->conn = $db; // $db es un objeto PDO
    }

    // Obtener usuario por ID
    public function getUserById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por correo
    public function getUserByEmail($correo) {
        $sql = "SELECT * FROM {$this->table} WHERE correo = :correo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":correo" => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear usuario
    public function createUser($nombre, $correo, $contrasena, $fotoPerfil = null) {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO {$this->table} (nombre_usuario, correo, contrasena, foto_perfil)
                VALUES (:nombre, :correo, :contrasena, :foto)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":nombre" => $nombre,
            ":correo" => $correo,
            ":contrasena" => $hash,
            ":foto" => $fotoPerfil ?? 'assets/uploads/default.jpeg'
        ]);
    }

    // Actualizar usuario
    public function updateUser($id, $nombre, $correo, $contrasena = null, $fotoPerfil = null) {
        $query = "UPDATE {$this->table} SET nombre_usuario = :nombre, correo = :correo";
        $params = [
            ":nombre" => $nombre,
            ":correo" => $correo,
            ":id" => $id
        ];

        if (!empty($contrasena)) {
            $query .= ", contrasena = :contrasena";
            $params[":contrasena"] = password_hash($contrasena, PASSWORD_BCRYPT);
        }

        if (!empty($fotoPerfil)) {
            $query .= ", foto_perfil = :foto";
            $params[":foto"] = $fotoPerfil;
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    // Verificar credenciales
    public function verifyCredentials($correo, $password) {
        $user = $this->getUserByEmail($correo);
        if ($user && password_verify($password, $user['contrasena'])) {
            return $user;
        }
        return false;
    }
}
