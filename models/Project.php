<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Database

class Projects{
    public static function create($name, $description, $owner_id){

        $conexion = Database::connect(); // Obtiene la conexión mysqli llamando Database::connect()

        $sentencia = $conexion->prepare("INSERT INTO projects (name, description, owner_id) VALUES (?, ?, ?)"); // Prepara la consulta SQL con placeholders (?)
        if (!$sentencia) return false;
        $sentencia->bind_param("ssi", $name, $description, $owner_id); // Enlaza los parámetros (s=string, i=int)

        return $sentencia->execute(); // Ejecuta la consulta y devuelve true/false

    }

    // Funcion para obtener todos los proyectos
    public static function all() {

        $conexion = Database::connect(); // Conexión mysqli

        $result = $conexion->query("SELECT * FROM projects ORDER BY created_at DESC"); // Ejecuta la consulta

        return $result->fetch_all(MYSQLI_ASSOC); // Devuelve todas las filas como un array asociativo

    }

    // Funcion para obtener una tarea por id
    public static function buscar($id) {
        $conexion = Database::connect();

        $sentencia = $conexion->prepare("SELECT * FROM projects WHERE id = ?");
        $sentencia->bind_param("i", $id);
        $sentencia->execute();

        return $sentencia->get_result()->fetch_assoc();
    }

    // Funcion para actualizar un proyecto
    public static function update($id, $name, $description) {
        $conexion = Database::connect();

        $sentencia = $conexion->prepare("UPDATE projects SET name=?, description=? WHERE id=?");
        $sentencia->bind_param("ssi", $name, $description, $id);

        return $sentencia->execute();
    }

    public static function delete($id){
        $conexion = Database::connect();

        $sentencia = $conexion->prepare("DELETE FROM projects WHERE id = ?");
        $sentencia->bind_param("i", $id);

        return $sentencia->execute();
    }

}