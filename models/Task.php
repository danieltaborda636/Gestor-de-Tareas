<?php
// models/Task.php
require_once __DIR__ . "/../config/Database.php";

class Task {
    private $conn;
    private $table = "tasks";

    public function __construct($db) {
        $this->conn = $db; // Objeto PDO
    }

    // 🔹 Registrar historial
    private function logHistorial($usuarioId, $accion, $detalle) {
        $sql = "INSERT INTO historial (usuario_id, accion, detalle, fecha) 
                VALUES (:usuario_id, :accion, :detalle, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":usuario_id" => $usuarioId,
            ":accion"     => $accion,
            ":detalle"    => $detalle
        ]);
    }

    // Crear nueva tarea
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
            (title, description, creator_id, assignee_id, project_id, parent_task_id, status, priority, start_date, due_date, recurrence_rule, position) 
            VALUES (:title, :description, :creator_id, :assignee_id, :project_id, :parent_task_id, :status, :priority, :start_date, :due_date, :recurrence_rule, :position)";

        $stmt = $this->conn->prepare($sql);

        $result = $stmt->execute([
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

        if ($result) {
            $this->logHistorial($data['creator_id'], 'Creación de Tarea', "Tarea '{$data['title']}' creada");
        }

        return $result;
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

    // Obtener tareas por usuario
    public function getByUser($userId) {
        $sql = "SELECT t.*, u.nombre_usuario AS assignee, p.name AS project_name 
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.assignee_id = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.creator_id = :userId OR t.assignee_id = :userId
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar una tarea por id
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar tarea
    public function update($id, $data, $usuarioId) {
        $sql = "UPDATE {$this->table} 
                SET title = :title, 
                    description = :description, 
                    assignee_id = :assignee_id,
                    project_id = :project_id, 
                    parent_task_id = :parent_task_id, 
                    status = :status, 
                    priority = :priority, 
                    start_date = :start_date,
                    due_date = :due_date, 
                    recurrence_rule = :recurrence_rule, 
                    position = :position, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $result = $stmt->execute([
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

        if ($result) {
            $this->logHistorial($usuarioId, 'Actualización de Tarea', "Tarea '{$data['title']}' actualizada");
        }

        return $result;
    }

    // Eliminar tarea
    public function delete($id, $usuarioId) {
        $task = $this->find($id);
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([":id" => $id]);

        if ($result && $task) {
            $this->logHistorial($usuarioId, 'Eliminación de Tarea', "Tarea '{$task['title']}' eliminada");
        }

        return $result;
    }
}
