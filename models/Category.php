<?php
require_once __DIR__ . "/../config/Database.php"; // Incluye la clase Database
session_start();
//crea la clase de categoria
class Category
{

    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function CrearCategoria($nombre)
    {
        $sentencia = $this->conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $sentencia->bind_param("s", $nombre);

          if ($sentencia->execute()) {
            //ENVIAR MENSAJE DE EXITO
            $_SESSION["mensaje"] = "Categoria creada con exito";
        } else {
            $_SESSION["error"] = "Error al crear la categoria";
        }
    }

    public function UpdateCategoria($nombre)
    {
        $sentencia = $this->conn->prepare("UPDATE categorias SET nombre = ?");
        $sentencia->bind_param("s", $nombre);

        if ($sentencia->execute()) {
            //ENVIAR MENSAJE DE EXITO
            $_SESSION["mensaje"] = "Categoria actualizada con exito";
        } else {
            $_SESSION["error"] = "Error al actualizar la categoria";
        }
    }

    public function DeleteCategoria($id)
    {
        $sentencia = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
        $sentencia->bind_param("i", $id);

        if ($sentencia->execute()) {
            //ENVIAR MENSAJE DE EXITO
            $_SESSION["mensaje"] = "Categoria eliminada con exito";
        } else {
            $_SESSION["mensaje"] = "Error al eliminar la categoria";
        }
    }


}