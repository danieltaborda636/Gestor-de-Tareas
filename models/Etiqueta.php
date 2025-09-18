<?php
// models/Etiqueta.php
require_once __DIR__ . "/../config/Database.php";
session_start();

class Etiqueta
{
    private $conn;
    private $table = "etiquetas";

    // Acepta opcionalmente una conexión PDO (útil para testing); si no, crea una nueva
    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Databasee();
            $this->conn = $database->getConnection();
        }
    }

    // Devuelve todas las etiquetas activas
    public function all()
    {
        $sql = "SELECT * FROM {$this->table} WHERE estado = 'activo' ORDER BY nombre_etiqueta";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener etiquetas asociadas a una tarea
    public function getByTask($task_id)
    {
        $sql = "SELECT e.* 
                FROM etiquetas e
                INNER JOIN task_labels tl ON e.id = tl.etiqueta_id
                WHERE tl.task_id = :task_id
                ORDER BY e.nombre_etiqueta";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":task_id" => $task_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reemplaza las etiquetas de una tarea.
     * $labels debe ser un array de ids (enteros). 
     * Devuelve true/false.
     */
    public function setForTask($task_id, $labels = [])
    {
        try {
            $this->conn->beginTransaction();

            // Eliminar etiquetas actuales
            $del = $this->conn->prepare("DELETE FROM task_labels WHERE task_id = :task_id");
            $del->execute([":task_id" => $task_id]);

            // Insertar nuevas (si las hay)
            if (!empty($labels) && is_array($labels)) {
                $ins = $this->conn->prepare("INSERT INTO task_labels (task_id, etiqueta_id) VALUES (:task_id, :etiqueta_id)");
                foreach ($labels as $label_id) {
                    $label_id = (int)$label_id;
                    if ($label_id <= 0) continue; // prevenir inserts inválidos
                    $ins->execute([":task_id" => $task_id, ":etiqueta_id" => $label_id]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $_SESSION['error'] = "Error al guardar etiquetas: " . $e->getMessage();
            return false;
        }
    }

    // Crear nueva etiqueta (mantengo tu API original)
    public function CrearEtiqueta($nombre_etiqueta, $color)
    {
        $nombre_etiqueta = trim($nombre_etiqueta);
        $color = trim($color);

        // Verificar existencia
        $check = $this->conn->prepare("SELECT id FROM {$this->table} WHERE nombre_etiqueta = :nombre LIMIT 1");
        $check->execute([":nombre" => $nombre_etiqueta]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION["error"] = "La etiqueta '{$nombre_etiqueta}' ya existe";
            return false;
        }

        // Insertar
        $sentencia = $this->conn->prepare("INSERT INTO {$this->table} (nombre_etiqueta, color) VALUES (:nombre, :color)");
        $ok = $sentencia->execute([":nombre" => $nombre_etiqueta, ":color" => $color]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta creada con éxito";
            return true;
        } else {
            $_SESSION["error"] = "Error al crear la etiqueta";
            return false;
        }
    }

    // Actualizar etiqueta (recibe id y nombre nuevo)
    public function UpdateEtiqueta($id, $nombre_etiqueta)
    {
        $id = (int)$id;
        $nombre_etiqueta = trim($nombre_etiqueta);

        // Verificar si ya existe otra etiqueta con ese nombre
        $check = $this->conn->prepare("SELECT id FROM {$this->table} WHERE nombre_etiqueta = :nombre AND id != :id LIMIT 1");
        $check->execute([":nombre" => $nombre_etiqueta, ":id" => $id]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION["error"] = "La etiqueta '{$nombre_etiqueta}' ya existe";
            return false;
        }

        // Actualizar
        $sentencia = $this->conn->prepare("UPDATE {$this->table} SET nombre_etiqueta = :nombre WHERE id = :id");
        $ok = $sentencia->execute([":nombre" => $nombre_etiqueta, ":id" => $id]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta actualizada con éxito";
            return true;
        } else {
            $_SESSION["error"] = "Error al actualizar la etiqueta";
            return false;
        }
    }

    // Eliminar etiqueta por id
    public function DeleteEtiqueta($id)
    {
        $id = (int)$id;
        $sentencia = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $ok = $sentencia->execute([":id" => $id]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta eliminada con éxito";
            return true;
        } else {
            $_SESSION["error"] = "Error al eliminar la etiqueta";
            return false;
        }
    }
}
