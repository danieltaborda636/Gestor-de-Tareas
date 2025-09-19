<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Databasee

class Projects {

    // Crear un proyecto
    public static function create($name, $description, $owner_id) {
        $conexion = Databasee::connect();

        $sql = "INSERT INTO projects (name, description, owner_id) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        return $stmt->execute([$name, $description, $owner_id]);
    }

    // Obtener todos los proyectos
    public static function all() {
        $conexion = Databasee::connect();

        $sql = "SELECT * FROM projects ORDER BY created_at DESC";
        $stmt = $conexion->query($sql);

        return $stmt->fetchAll(); // Devuelve un array asociativo
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
