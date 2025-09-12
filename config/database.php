<?php

class Database {
    public static function connect(){
        if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
        if (!defined('DB_USERNAME')) define("DB_USERNAME", "root");
        if (!defined('DB_PASSWORD')) define("DB_PASSWORD", '');
        if (!defined('DB_NAME')) define('DB_NAME', 'gestortareas');

        $conexion = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if (!$conexion) {
            die("Error de conexión: " . mysqli_connect_error());
        }
        return $conexion;
    }
}

?>
<?php
// models/Database.php
class Databasee {
    private $host = "localhost";
    private $user = "root";       // cambia si tu usuario es otro
    private $pass = "";           // cambia si tu contraseña es otra
    private $dbname = "gestortareas"; // cambia por el nombre de tu BD

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

            if ($this->conn->connect_error) {
                die("Error de conexión: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }

        return $this->conn;
    }
}