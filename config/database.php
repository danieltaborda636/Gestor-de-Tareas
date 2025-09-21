<?php
// config/Database.php
// Clase para gestionar la conexión a la base de datos usando PDO

class Databasee {
    private $host = "localhost";      // Cambia según tu configuración
    private $db_name = "gestortareas"; // Nombre de tu base de datos
    private $username = "root";       // Usuario de tu BD
    private $password = "";           // Contraseña de tu BD
    private $conn;

    // Devuelve una conexión PDO
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );

            // Configuraciones recomendadas de PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            echo "❌ Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
     // ✅ Método estático para llamarlo como Databasee::connect()
    public static function connect() {
        $instance = new self();
        return $instance->getConnection();
    }
}
?>

<?php
define("BASE_URL", "http://localhost/proyecto-1/Gestor-de-Tareas/");
define("UPLOADS_URL", BASE_URL . "assets/uploads/");

