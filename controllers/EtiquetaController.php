<?php
require_once __DIR__ . "/../models/Etiqueta.php"; // Incluye el modelo Categoria
session_start();
//crea la clase de categoria
class EtiquetaController
{

    public function CrearEtiqueta()
{
    $modeloEtiqueta = new Etiqueta(); // Usamos el modelo correcto

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validamos que existan los campos
        if (!empty($_POST["nombre"]) && !empty($_POST["color"])) {
            $nombre_etiqueta = trim($_POST["nombre"]);
            $color = trim($_POST["color"]);

            // Llamamos al modelo para crear la etiqueta
            $modeloEtiqueta->CrearEtiqueta($nombre_etiqueta, $color);

        } else {
            $_SESSION["error"] = "Todos los campos son obligatorios";
        }
    }

    // Redirige a la página anterior
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}


    public function UpdateEtiqueta()
    {
        $modeloCategoria = new Category();
        //SI EL METODO ES IGUAL A PUT == PUT ES PARA ACTUALIZAR DATOS
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre_etiqueta = $_POST["nombre"];
            $modeloCategoria->UpdateCategoria($nombre_etiqueta);
        }
        header("Location:" . $_SERVER["HTTP_REFERER"]);

        exit();
    }

    public function DeleteEtiqueta()
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