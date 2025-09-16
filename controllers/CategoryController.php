<?php
require_once __DIR__ . "/../models/Category.php"; // Incluye el modelo Categoria
session_start();
//crea la clase de categoria
class CategoryController
{

    public function CrearCategoria()
    {
        $modeloCategoria = new Category();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["nombre"]) && !empty($_POST["nombre"])) {
                $nombre = $_POST["nombre"];
                $modeloCategoria->CrearCategoria($nombre);
            }else{
                $_SESSION["error"] = "Error campo vacio ";
            }
        }

        //ENVIA A LA PAGINA ANTERIOR
        header("Location:" . $_SERVER["HTTP_REFERER"]);

        exit();
    }

    public function UpdateCategoria()
    {
        $modeloCategoria = new Category();
        //SI EL METODO ES IGUAL A PUT == PUT ES PARA ACTUALIZAR DATOS
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = $_POST["nombre"];
            $modeloCategoria->UpdateCategoria($nombre);
        }
        header("Location:" . $_SERVER["HTTP_REFERER"]);

        exit();
    }

    public function DeleteCategoria()
    {
        $modeloCategoria = new Category();
        //SI EL METODO ES IGUAL A PUT == PUT ES PARA ACTUALIZAR DATOS
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST["id"];
            $modeloCategoria->DeleteCategoria($id);
        }


        header("Location:" . $_SERVER["HTTP_REFERER"]);
        exit();
    }


}
