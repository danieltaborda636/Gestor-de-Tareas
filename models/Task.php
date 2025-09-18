<?php
// models/Task.php
require_once __DIR__ . "/../config/Database.php";

class Task {
    private $conn;
    private $table = "tasks";

    public function __construct($db) {
        $this->conn = $db; // Objeto PDO
    }

    // Crear nueva tarea
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
            (title, description, creator_id, assignee_id, project_id, parent_task_id, status, priority, start_date, due_date, recurrence_rule, position) 
            VALUES (:title, :description, :creator_id, :assignee_id, :project_id, :parent_task_id,:status, :priority, :start_date, :due_date, :recurrence_rule, :position)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":title"          => $data['title'],
            ":description"    => $data['description'],
            ":creator_id"     => $data['creator_id'],
            ":assignee_id"    => $data['assignee_id'] ?? null,
            ":project_id"     => $data['project_id'] ?? null,
            ":parent_task_id" => $data['parent_task_id'] ?? null,
            ":status"         => $data['status'] ?? 'todo',
            ":priority"       => $data['priority'] ?? 'medium',
            ":start_date"     => $data['start_date'] ?? null,
            ":due_date"       => $data['due_date'] ?? null,
            ":recurrence_rule"=> $data['recurrence_rule'] ?? 'none',
            ":position"       => $data['position'] ?? 0
        ]);
    }

    // Obtener todas las tareas
    public function all() {
        $sql = "SELECT t.*, u.nombre_usuario AS assignee, p.name AS project 
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.assignee_id = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                ORDER BY t.created_at DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar una tarea por id
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar tarea
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET title = :title, description = :description, assignee_id = :assignee_id,
            project_id = :project_id, parent_task_id = :parent_task_id, status = :status, priority = :priority, start_date = :start_date,
            due_date = :due_date, recurrence_rule = :recurrence_rule, position = :position, updated_at = NOW() WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":id"             => $id,
            ":title"          => $data['title'],
            ":description"    => $data['description'],
            ":assignee_id"    => $data['assignee_id'] ?? null,
            ":project_id"     => $data['project_id'] ?? null,
            ":parent_task_id" => $data['parent_task_id'] ?? null,
            ":status"         => $data['status'],
            ":priority"       => $data['priority'],
            ":start_date"     => $data['start_date'] ?? null,
            ":due_date"       => $data['due_date'] ?? null,
            ":recurrence_rule"=> $data['recurrence_rule'] ?? 'none',
            ":position"       => $data['position'] ?? 0
        ]);
    }

    // Eliminar tarea
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }
}
