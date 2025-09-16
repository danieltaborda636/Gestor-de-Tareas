<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Database

class Projects{
    public static function create($name, $description, $owner_id, $fecha_registro){

        $conexion = Database::connect(); // Obtiene la conexión mysqli llamando Database::connect()

        $sentencia = $conexion->prepare("INSERT INTO projects (name, description, owner_id, created_at) VALUES (?, ?, ?, ?)"); // Prepara la consulta SQL con placeholders (?)
        if (!$sentencia) return false;
        $sentencia->bind_param("ssis", $name, $description, $owner_id, $fecha_registro); // Enlaza los parámetros (s=string, i=int)

        return $sentencia->execute(); // Ejecuta la consulta y devuelve true/false

    }

    // Funcion para obtener todos los proyectos
    public static function all() {

        $conexion = Database::connect(); // Conexión mysqli

        $result = $conexion->query("SELECT * FROM projects ORDER BY created_at DESC"); // Ejecuta la consulta

        return $result->fetch_all(MYSQLI_ASSOC); // Devuelve todas las filas como un array asociativo

    }


}