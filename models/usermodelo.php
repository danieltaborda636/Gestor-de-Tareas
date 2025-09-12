<?php
// models/User.php
// Modelo que representa la tabla "usuarios"

class User {
    // --- 1) Atributos ---
    private $conn;          // Conexión a la base de datos
    private $table = "usuarios"; // Nombre de la tabla en la BD

    // --- 2) Constructor ---
    // Recibe la conexión mysqli desde Database.php
    public function __construct($db) {
        $this->conn = $db;
    }

    // --- 3) Obtener un usuario por su ID ---
    public function getUserById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // --- 4) Actualizar usuario ---
    /**
     * @param int $id                ID del usuario
     * @param string $nombre         Nuevo nombre
     * @param string $correo         Nuevo correo
     * @param string|null $contrasena Nueva contraseña (si no cambia, null)
     * @param string|null $fotoPerfil Nueva ruta de foto (si no cambia, null)
     * 
     * @return bool true si la actualización fue exitosa, false en caso contrario
     */
    public function updateUser($id, $nombre, $correo, $contrasena = null, $fotoPerfil = null) {
        // --- Construcción dinámica de la query ---
        $query = "UPDATE " . $this->table . " SET nombre_usuario = ?, correo = ?";
        $params = [$nombre, $correo];
        $types = "ss"; // dos strings: nombre y correo

        // Si el usuario cambió la contraseña
        if (!empty($contrasena)) {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT); // siempre se guarda hasheada
            $query .= ", contrasena = ?";
            $params[] = $hash;
            $types .= "s";
        }

        // Si el usuario subió una nueva foto
        if (!empty($fotoPerfil)) {
            $query .= ", foto_perfil = ?";
            $params[] = $fotoPerfil;
            $types .= "s";
        }

        // Siempre actualizamos el registro por ID
        $query .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i"; // entero

        // --- Ejecutamos la query preparada ---
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false; // fallo en la preparación
        }

        $stmt->bind_param($types, ...$params);

        return $stmt->execute(); // true si se ejecutó, false si hubo error
    }
}
