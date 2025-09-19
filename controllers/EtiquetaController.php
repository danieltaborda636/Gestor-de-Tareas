<?php
require_once __DIR__ . "/../models/Etiqueta.php";

class EtiquetaController
{
    public function CrearEtiqueta()
    {
        $modeloEtiqueta = new Etiqueta();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!empty($_POST["nombre_etiqueta"]) && !empty($_POST["color"])) {
                $nombre_etiqueta = trim($_POST["nombre_etiqueta"]);
                $color = trim($_POST["color"]);

                // Crear etiqueta (el modelo ya registra el historial)
                $modeloEtiqueta->CrearEtiqueta($nombre_etiqueta, $color);
            } else {
                $_SESSION["error"] = "Todos los campos son obligatorios";
            }
        }

        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit();
    }

    public function UpdateEtiqueta()
    {
        $modeloEtiqueta = new Etiqueta();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST["id"];
            $nombre_etiqueta = trim($_POST["nombre_etiqueta"]);

            // Actualizar etiqueta (historial ya en modelo)
            $modeloEtiqueta->UpdateEtiqueta($id, $nombre_etiqueta);
        }

        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit();
    }

    public function DeleteEtiqueta()
    {
        $modeloEtiqueta = new Etiqueta();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = $_POST["id"];
            // Eliminar etiqueta (historial ya en modelo)
            $modeloEtiqueta->DeleteEtiqueta($id);
        }

        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit();
    }
}
