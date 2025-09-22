<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Databasee

class Projects {

    // Crear un proyecto
    public static function create($name, $description, $owner_id) {
        $conexion = Databasee::connect();

        $sql = "INSERT INTO projects (name, description, owner_id) 
                VALUES (:name, :description, :owner_id)";
        $stmt = $conexion->prepare($sql);

        return $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":owner_id" => $owner_id
        ]);
    }

    // Obtener proyectos según rol
    public static function getByUser($userId, $rol) {
        $conexion = Databasee::connect();

        $sql = "SELECT p.*, u.nombre_usuario AS creador_nombre
                FROM projects p
                LEFT JOIN usuarios u ON p.owner_id = u.id";

        $params = [];

        if ($rol !== 'admin') {
            $sql .= " WHERE p.owner_id = :userId";
            $params[':userId'] = $userId;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar un proyecto por id
    public static function buscar($id) {
        $conexion = Databasee::connect();

        $sql = "SELECT * FROM projects WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(); // Devuelve una sola fila
    }

    // Actualizar un proyecto
    public static function update($id, $name, $description) {
        $conexion = Databasee::connect();

        $sql = "UPDATE projects SET name = ?, description = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);

        return $stmt->execute([$name, $description, $id]);
    }

    // Eliminar un proyecto
    public static function delete($id) {
        $conexion = Databasee::connect();

        $sql = "DELETE FROM projects WHERE id = ?";
        $stmt = $conexion->prepare($sql);

        return $stmt->execute([$id]);
    }
}
