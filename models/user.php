<?php
// models/User.php
// Modelo para la tabla "usuarios" usando PDO

class User {
    private $conn;
    private $table = "usuarios";

    public function __construct($db) {
        $this->conn = $db; // $db es un objeto PDO
    }

    // Crear nuevo usuario (siempre como miembro por defecto)
    public function create($nombre, $correo, $passwordHash) {
        $sql = "INSERT INTO {$this->table} (nombre_usuario, correo, contrasena, foto_perfil, rol) 
                VALUES (:nombre, :correo, :contrasena, :foto, 'miembro')";
        $stmt = $this->conn->prepare($sql);

        $fotoDefecto = "assets/uploads/default.jpeg";
        return $stmt->execute([
            ":nombre" => $nombre,
            ":correo" => $correo,
            ":contrasena" => $passwordHash,
            ":foto" => $fotoDefecto
        ]);
    }

    // Buscar usuario por correo
    public function findByEmail($correo) {
        $sql = "SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":correo" => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar usuario por id o correo
    public function findByEmailOrId($idOrEmail) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id OR correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $idOrEmail, ":correo" => $idOrEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar credenciales
    public function verifyCredentials($correo, $password) {
        $user = $this->findByEmail($correo);
        if ($user && password_verify($password, $user['contrasena'])) {
            return $user;
        }
        return false;
    }

    // Obtener usuario por ID
    public function getUserById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar usuario
    public function updateUser($id, $nombre, $correo, $contrasena = null, $fotoPerfil = null) {
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

        $sql .= " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}
