<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Database

/* Clase Task con métodos estáticos para operaciones CRUD */
class Task {
    // Funcion para crear una nueva tarea
    public static function create($title, $description, $creator_id, $priority = 'medium', $status = 'todo') {

        $conexion = Database::connect(); // Obtiene la conexión mysqli llamando Database::connect()

        // 👇 Incluimos el campo creator_id en el INSERT
        $sentencia = $conexion->prepare("INSERT INTO tasks (title, description, creator_id, priority, status) VALUES (?, ?, ?, ?, ?)"); 
        if (!$sentencia) return false;

        // 👇 Vinculamos creator_id como entero (i)
        $sentencia->bind_param("ssiss", $title, $description, $creator_id, $priority, $status);

        return $sentencia->execute(); // Ejecuta la consulta y devuelve true/false
    }

    // Funcion para obtener todas las tareas
    public static function all() {
        $conexion = Database::connect();
        $result = $conexion->query("SELECT * FROM tasks ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Funcion para obtener una tarea por id
    public static function buscar($id) {
        $conexion = Database::connect();
        $sentencia = $conexion->prepare("SELECT * FROM tasks WHERE id = ?");
        $sentencia->bind_param("i", $id);
        $sentencia->execute();
        return $sentencia->get_result()->fetch_assoc();
    }

    // Funcion para actualizar una tarea
    public static function update($id, $title, $description, $priority, $status) {
        $conexion = Database::connect();
        $sentencia = $conexion->prepare("UPDATE tasks SET title=?, description=?, priority=?, status=? WHERE id=?");
        $sentencia->bind_param("ssssi", $title, $description, $priority, $status, $id);
        return $sentencia->execute();
    }

    // Funcion para eliminar una tarea por id
    public static function delete($id) {
        $conexion = Database::connect();
        $sentencia = $conexion->prepare("DELETE FROM tasks WHERE id = ?");
        $sentencia->bind_param("i", $id);
        return $sentencia->execute();
    }
}
