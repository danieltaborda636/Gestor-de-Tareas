<?php
require_once __DIR__ . "/../config/database.php"; // Incluye la clase Database
session_start();
//se crera la clase de la etiqueta
class Etiqueta
{

    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function CrearEtiqueta($nombre_etiqueta, $color)
    {
    $nombre_etiqueta = trim($nombre_etiqueta);
    $color = trim($color);

    // Verificar existencia
    $check = $this->conn->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ? LIMIT 1");
    $check->bind_param("s", $nombre_etiqueta);
    $check->execute();
    $result = $check->get_result();

    if ($result && $result->num_rows > 0) {
        $_SESSION["error"] = "La etiqueta '$nombre_etiqueta' ya existe";
        $check->close();
        return;
    }
    $check->close();

    // Insertar si no existe
    $sentencia = $this->conn->prepare("INSERT INTO etiquetas (nombre_etiqueta, color) VALUES (?, ?)");
    $sentencia->bind_param("ss", $nombre_etiqueta, $color);

    if ($sentencia->execute()) {
        $_SESSION["mensaje"] = "Etiqueta creada con éxito";
    } else {
        $_SESSION["error"] = "Error al crear la etiqueta";
    }
    $sentencia->close();
}




    //creamos la sentencia para modificar la Etiqueta
    public function UpdateEtiqueta($nombre_etiqueta)
    {

         // Preparas una consulta preparada para buscar si existe una fila con ese nombre
        $check = $this->conn->prepare("SELECT id FROM etiquetas WHERE nombre_etiqueta = ? LIMIT 1");

        // Asociar el valor que llega ($nombre_etiqueta) al placeholder (?) de la consulta
        $check->bind_param("s", $nombre_etiqueta);

        // Ejecutar la consulta en la base de datos
        $check->execute();

        // Obtener el conjunto de resultados (mysqli_result)
        $result = $check->get_result();

        // Si num_rows > 0 significa que hay al menos una fila: la etiqueta ya existe
        if ($result->num_rows > 0) {
        // Guardas en sesión un mensaje de error que luego puedes mostrar en la vista
        $_SESSION["error"] = "La etiqueta '$nombre_etiqueta' ya existe";
        // Cierras el statement para liberar recursos
        $check->close();
        // Terminas la función (no se hace el INSERT)
        return;
        }

        // Si llegas aquí no existe la etiqueta (no se encontró), cierras el statement
        $check->close();

        // al no existir la etiqueta se modificara  en la base de datos

        $sentencia = $this->conn->prepare("UPDATE etiquetas SET nombre_etiqueta = ? WHERE id = ?");
        $sentencia->bind_param("si", $nombre_etiqueta, $id);

        if ($sentencia->execute()) {
            //ENVIAR MENSAJE DE EXITO
            $_SESSION["mensaje"] = "Etiqueta actualizada con exito";
        } else {
            $_SESSION["error"] = "Error al actualizar la Etiqueta";
        }
         $sentencia->close();
    }

    //se hace la sentencia para eliminar la Etiqueta
    public function DeleteEtiqueta($id)
    {
        $sentencia = $this->conn->prepare("DELETE FROM etiquetas WHERE id = ?");
        $sentencia->bind_param("i", $id);

        if ($sentencia->execute()) {
            //ENVIAR MENSAJE DE EXITO
            $_SESSION["mensaje"] = "etiqueta eliminada con exito";
        } else {
            $_SESSION["mensaje"] = "Error al eliminar la etiqueta";
        }
    }

}

?>