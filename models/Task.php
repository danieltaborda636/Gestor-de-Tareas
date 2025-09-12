<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Database

/* Clase Task con métodos estáticos para operaciones CRUD */
class Task {
    // Funcion para crear una nueva tarea
    public static function create($title, $description, $creator_id, $priority = 'medium', $status = 'todo') {

        $conexion = Database::connect(); // Obtiene la conexión mysqli llamando Database::connect()

        $sentencia = $conexion->prepare("INSERT INTO tasks (title, description, creator_id, priority, status) VALUES (?, ?, ?, ?, ?)"); // Prepara la consulta SQL con placeholders (?)
        if (!$sentencia) return false;
        $sentencia->bind_param("ssiss", $title, $description, $creator_id, $priority, $status); // Enlaza los parámetros (s=string, i=int)

        return $sentencia->execute(); // Ejecuta la consulta y devuelve true/false

    }

    // Funcion para obtener todas las tareas
    public static function all() {

        $conexion = Database::connect(); // Conexión mysqli

        $result = $conexion->query("SELECT * FROM tasks ORDER BY created_at DESC"); // Ejecuta la consulta

        return $result->fetch_all(MYSQLI_ASSOC); // Devuelve todas las filas como un array asociativo

    }

    // Funcion para obtener una tarea por id
    public static function buscar($id) {

        $conexion = Database::connect(); // Conexión

        $sentencia = $conexion->prepare("SELECT * FROM tasks WHERE id = ?"); // Prepara la consulta
        $sentencia->bind_param("i", $id); // Enlaza el id (i=int)
        $sentencia->execute(); // Ejecuta la consulta

        return $sentencia->get_result()->fetch_assoc(); // Devuelve la fila como un array asociativo

    }

    // Funcion para actualizar una tarea
    public static function update($id, $title, $description, $priority, $status) {

        $conexion = Database::connect(); // Conexión

        $sentencia = $conexion->prepare("UPDATE tasks SET title=?, description=?, priority=?, status=? WHERE id=?"); // Prepara la sentencia UPDATE
        $sentencia->bind_param("ssssi", $title, $description, $priority, $status, $id); // Enlaza los parámetros

        return $sentencia->execute(); // Ejecuta y devuelve true/false

    }

    // Funcion para eliminar una tarea por id
    public static function delete($id) {

        $conexion = Database::connect(); // Conexión

        $sentencia = $conexion->prepare("DELETE FROM tasks WHERE id = ?"); // Prepara la sentencia DELETE
        $sentencia->bind_param("i", $id); // Enlaza el id (i=int)

        return $sentencia->execute(); // Ejecuta y devuelve true/false
        
    }
    
}