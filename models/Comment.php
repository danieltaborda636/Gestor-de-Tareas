<?php
// models/Comment.php
require_once __DIR__ . "/../config/Database.php";

class Comment {
    private $conn;
    private $table = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear comentario
    public function create($taskId, $userId, $body) {
        $sql = "INSERT INTO {$this->table} (task_id, user_id, body) VALUES (:task_id, :user_id, :body)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ":task_id" => $taskId,
            ":user_id" => $userId,
            ":body"    => $body
        ]);
    }

    // Listar comentarios de una tarea
    public function allByTask($taskId) {
        $sql = "SELECT c.*, u.nombre_usuario 
                FROM {$this->table} c
                JOIN usuarios u ON c.user_id = u.id
                WHERE c.task_id = :task_id
                ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":task_id" => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar comentario
    public function update($id, $body) {
        $sql = "UPDATE {$this->table} SET body = :body, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id, ":body" => $body]);
    }

    // Eliminar comentario
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }
}
?>