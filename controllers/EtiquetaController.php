<?php
require_once __DIR__ . "/../models/Etiqueta.php"; // Incluye el modelo Etiqueta
//crea la clase de Etiqueta
class EtiquetaController
{

    public function CrearEtiqueta()
    {
        $modeloEtiqueta = new Etiqueta(); // Usamos el modelo correcto

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validamos que existan los campos
            if (!empty($_POST["nombre_etiqueta"]) && !empty($_POST["color"])) {
                $nombre_etiqueta = trim($_POST["nombre_etiqueta"]);
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
        $modeloEtiqueta = new Etiqueta();
        //SI EL METODO ES IGUAL A PUT == PUT ES PARA ACTUALIZAR DATOS
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre_etiqueta = $_POST["nombre_etiqueta"];
            $modeloEtiqueta->UpdateEtiqueta($nombre_etiqueta);
        }
        header("Location:" . $_SERVER["HTTP_REFERER"]);

        exit();
    }

    public function DeleteEtiqueta()
    {
        $modeloEtiqueta = new Etiqueta();
        //SI EL METODO ES IGUAL A PUT == PUT ES PARA ACTUALIZAR DATOS
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST["id"];
            $modeloEtiqueta->DeleteEtiqueta($id);
        }


        header("Location:" . $_SERVER["HTTP_REFERER"]);
        exit();
    }


}