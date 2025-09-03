<?php
require_once __DIR__ . '/../config/database.php'; //Importamos la base de datos

class User{
    private $conn;

    public function __construct() {
        $this->conn = Database::connect(); // Establece la conexión al instanciar la clase
    }
}

?>