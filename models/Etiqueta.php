<?php
// models/Etiqueta.php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/Historial.php"; // Para registrar acciones en historial

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Etiqueta
{
    private $conn;
    private $table = "etiquetas";
    private $historial;

    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            $database = new Databasee();
            $this->conn = $database->getConnection();
        }

        $this->historial = new Historial($this->conn); // Instancia historial usando la misma conexión
    }

    // Obtener etiquetas según rol
    public function getByUser($userId, $rol) {
        $sql = "SELECT e.*, u.nombre_usuario as creador_nombre
                FROM etiquetas e
                LEFT JOIN usuarios u ON e.user_id = u.id";

        $params = [];

        if ($rol !== 'admin') {
            $sql .= " WHERE e.user_id = :userId";
            $params[':userId'] = $userId;
        }

        $sql .= " ORDER BY e.fecha_creacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

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

    // Reemplaza las etiquetas de una tarea
    public function setForTask($task_id, $labels = [])
    {
        try {
            $this->conn->beginTransaction();

            $del = $this->conn->prepare("DELETE FROM task_labels WHERE task_id = :task_id");
            $del->execute([":task_id" => $task_id]);

            if (!empty($labels) && is_array($labels)) {
                $ins = $this->conn->prepare("INSERT INTO task_labels (task_id, etiqueta_id) VALUES (:task_id, :etiqueta_id)");
                foreach ($labels as $label_id) {
                    $label_id = (int)$label_id;
                    if ($label_id <= 0) continue;
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

    // Crear nueva etiqueta con historial
    public function CrearEtiqueta($nombre_etiqueta, $color)
    {
        $nombre_etiqueta = trim($nombre_etiqueta);
        $color = trim($color);

        $check = $this->conn->prepare("SELECT id FROM {$this->table} WHERE user_id = :userId AND nombre_etiqueta = :nombre LIMIT 1");
        $check->execute([":nombre" => $nombre_etiqueta, ":userId" => $_SESSION['user']['id']]);
        if ($check->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION["error"] = "La etiqueta '{$nombre_etiqueta}' ya existe para este usuario";
            return false;
        }

        // Guardar con user_id
        $sentencia = $this->conn->prepare("INSERT INTO {$this->table} (user_id, nombre_etiqueta, color) 
                                        VALUES (:userId, :nombre, :color)");
        $ok = $sentencia->execute([
            ":userId" => $_SESSION['user']['id'] ?? null,
            ":nombre" => $nombre_etiqueta, 
            ":color" => $color
        ]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta creada con éxito";

            if (isset($_SESSION['user']['id'])) {
                $this->historial->add($_SESSION['user']['id'], 'create', "Creó la etiqueta '$nombre_etiqueta'");
            }
            return true;
        } else {
            $_SESSION["error"] = "Error al crear la etiqueta";
            return false;
        }
    }

    // Actualizar etiqueta con historial
    public function UpdateEtiqueta($id, $nombre_etiqueta)
    {
        $id = (int)$id;
        $nombre_etiqueta = trim($nombre_etiqueta);

        $check = $this->conn->prepare("SELECT id FROM {$this->table} WHERE nombre_etiqueta = :nombre AND id != :id LIMIT 1");
        $check->execute([":nombre" => $nombre_etiqueta, ":id" => $id]);
        if ($check->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION["error"] = "La etiqueta '{$nombre_etiqueta}' ya existe";
            return false;
        }

        $sentencia = $this->conn->prepare("UPDATE {$this->table} SET nombre_etiqueta = :nombre WHERE id = :id");
        $ok = $sentencia->execute([":nombre" => $nombre_etiqueta, ":id" => $id]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta actualizada con éxito";

            if (isset($_SESSION['user']['id'])) {
                $this->historial->add($_SESSION['user']['id'], 'update', "Actualizó la etiqueta ID $id a '$nombre_etiqueta'");
            }

            return true;
        } else {
            $_SESSION["error"] = "Error al actualizar la etiqueta";
            return false;
        }
    }

    // Eliminar etiqueta con historial
    public function DeleteEtiqueta($id)
    {
        $id = (int)$id;

        // Obtener nombre antes de eliminar
        $nombre_etiqueta = $this->getNombreEtiqueta($id);

        $sentencia = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $ok = $sentencia->execute([":id" => $id]);

        if ($ok) {
            $_SESSION["mensaje"] = "Etiqueta eliminada con éxito";

            if (isset($_SESSION['user']['id'])) {
                $this->historial->add($_SESSION['user']['id'], 'delete', "Eliminó la etiqueta ID $id '$nombre_etiqueta'");
            }

            return true;
        } else {
            $_SESSION["error"] = "Error al eliminar la etiqueta";
            return false;
        }
    }

    // Obtener nombre de etiqueta por ID
    public function getNombreEtiqueta($id)
    {
        $id = (int)$id;
        $stmt = $this->conn->prepare("SELECT nombre_etiqueta FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['nombre_etiqueta'] : null;
    }
}
