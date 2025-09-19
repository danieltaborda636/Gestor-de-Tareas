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
        // Normalizar valores opcionales
        $data['assignee_id']    = !empty($data['assignee_id']) ? $data['assignee_id'] : null;
        $data['project_id']     = !empty($data['project_id']) ? $data['project_id'] : null;
        $data['parent_task_id'] = !empty($data['parent_task_id']) ? $data['parent_task_id'] : null;
        $data['start_date']     = !empty($data['start_date']) ? $data['start_date'] : null;
        $data['due_date']       = !empty($data['due_date']) ? $data['due_date'] : null;
        $data['recurrence_rule'] = !empty($data['recurrence_rule']) ? $data['recurrence_rule'] : null;

        $sql = "INSERT INTO {$this->table} 
            (title, description, creator_id, assignee_id, project_id, parent_task_id, status, priority, start_date, due_date, recurrence_rule, position) 
            VALUES (:title, :description, :creator_id, :assignee_id, :project_id, :parent_task_id, :status, :priority, :start_date, :due_date, :recurrence_rule, :position)";

        $stmt = $this->conn->prepare($sql);

        $result = $stmt->execute([
            ":title"          => $data['title'],
            ":description"    => $data['description'],
            ":creator_id"     => $data['creator_id'],
            ":assignee_id"    => $data['assignee_id'],
            ":project_id"     => $data['project_id'],
            ":parent_task_id" => $data['parent_task_id'],
            ":status"         => $data['status'],
            ":priority"       => $data['priority'],
            ":start_date"     => $data['start_date'],
            ":due_date"       => $data['due_date'],
            ":recurrence_rule"=> $data['recurrence_rule'],
            ":position"       => $data['position']
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

    public function search($userId, $filters = []) {
        $sql = "SELECT t.*, u.nombre_usuario AS assignee, p.name AS project_name
                FROM {$this->table} t
                LEFT JOIN usuarios u ON t.assignee_id = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE (t.creator_id = :userId OR t.assignee_id = :userId)";

        $params = [":userId" => $userId];

        if (!empty($filters['q'])) {
            $sql .= " AND (t.title LIKE :q OR t.description LIKE :q)";
            $params[':q'] = "%" . $filters['q'] . "%";
        }
        if (!empty($filters['project_id'])) {
            $sql .= " AND t.project_id = :project_id";
            $params[':project_id'] = $filters['project_id'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['assignee_id'])) {
            $sql .= " AND t.assignee_id = :assignee_id";
            $params[':assignee_id'] = $filters['assignee_id'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND t.start_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['due_date'])) {
            $sql .= " AND t.due_date <= :due_date";
            $params[':due_date'] = $filters['due_date'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Actualizar tarea
    public function update($id, $data, $usuarioId) {
        // Normalizar valores opcionales
        $data['assignee_id']    = !empty($data['assignee_id']) ? $data['assignee_id'] : null;
        $data['project_id']     = !empty($data['project_id']) ? $data['project_id'] : null;
        $data['parent_task_id'] = !empty($data['parent_task_id']) ? $data['parent_task_id'] : null;
        $data['start_date']     = !empty($data['start_date']) ? $data['start_date'] : null;
        $data['due_date']       = !empty($data['due_date']) ? $data['due_date'] : null;
        $data['recurrence_rule'] = !empty($data['recurrence_rule']) ? $data['recurrence_rule'] : null;
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
            ":assignee_id"    => $data['assignee_id'],
            ":project_id"     => $data['project_id'],
            ":parent_task_id" => $data['parent_task_id'],
            ":status"         => $data['status'],
            ":priority"       => $data['priority'],
            ":start_date"     => $data['start_date'],
            ":due_date"       => $data['due_date'],
            ":recurrence_rule"=> $data['recurrence_rule'],
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
