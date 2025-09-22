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
        $hoy = date('Y-m-d');

        if (!empty($data['start_date']) && $data['start_date'] < $hoy) {
            throw new Exception("La fecha de inicio no puede ser anterior a hoy.");
        }
        if (!empty($data['due_date']) && $data['due_date'] < $hoy) {
            throw new Exception("La fecha de fin no puede ser anterior a hoy.");
        }

        $data['assignee_id']     = !empty($data['assignee_id']) ? $data['assignee_id'] : null;
        $data['project_id']      = !empty($data['project_id']) ? $data['project_id'] : null;
        $data['parent_task_id']  = !empty($data['parent_task_id']) ? $data['parent_task_id'] : null;
        $data['start_date']      = !empty($data['start_date']) ? $data['start_date'] : null;
        $data['due_date']        = !empty($data['due_date']) ? $data['due_date'] : null;
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

    // Obtener tareas por usuario y rol
    public function getByUser($userId, $rol) {
        $sql = "SELECT t.*, p.name AS project_name, tp.title AS parent_title, u.nombre_usuario as creador_nombre
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN tasks tp ON t.parent_task_id = tp.id
                LEFT JOIN usuarios u ON t.creator_id = u.id";

        if ($rol === 'admin') {
            $sql .= " ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql .= " WHERE t.creator_id = :userId OR t.assignee_id = :userId ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':userId' => $userId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar tareas con filtros según rol
    public function search($userId, $filtros, $rol) {
        $sql = "SELECT t.*, p.name AS project_name, tp.title AS parent_title, u.nombre_usuario as creador_nombre
            FROM tasks t
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN tasks tp ON t.parent_task_id = tp.id
            LEFT JOIN usuarios u ON t.creator_id = u.id";
        
        $params = [];

        if ($rol !== 'admin') {
            $sql .= " WHERE (t.creator_id = :userId OR t.assignee_id = :userId)";
            $params[':userId'] = $userId;
        } else {
            $sql .= " WHERE 1=1"; // admin ve todo
        }

        if (!empty($filtros['q'])) {
            $sql .= " AND (t.title LIKE :q OR t.description LIKE :q)";
            $params[':q'] = "%" . $filtros['q'] . "%";
        }
        if (!empty($filtros['project_id'])) {
            $sql .= " AND t.project_id = :project_id";
            $params[':project_id'] = $filtros['project_id'];
        }
        if (!empty($filtros['priority'])) {
            $sql .= " AND t.priority = :priority";
            $params[':priority'] = $filtros['priority'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filtros['status'];
        }
        if (!empty($filtros['assignee_id'])) {
            $sql .= " AND t.assignee_id = :assignee_id";
            $params[':assignee_id'] = $filtros['assignee_id'];
        }
        if (!empty($filtros['start_date'])) {
            $sql .= " AND t.start_date >= :start_date";
            $params[':start_date'] = $filtros['start_date'];
        }
        if (!empty($filtros['due_date'])) {
            $sql .= " AND t.due_date <= :due_date";
            $params[':due_date'] = $filtros['due_date'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar una tarea por id
    public function find($id) {
        $sql = "SELECT t.*, u1.nombre_usuario AS creador_nombre, u2.nombre_usuario AS asignado_nombre, p.name AS proyecto_nombre, tp.title AS parent_title
            FROM {$this->table} t
            LEFT JOIN usuarios u1 ON t.creator_id = u1.id
            LEFT JOIN usuarios u2 ON t.assignee_id = u2.id
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN tasks tp ON t.parent_task_id = tp.id
            WHERE t.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar
    public function update($id, $data, $usuarioId) {
        $hoy = date('Y-m-d');

        if (!empty($data['start_date']) && $data['start_date'] < $hoy) {
            throw new Exception("La fecha de inicio no puede ser anterior a hoy.");
        }
        if (!empty($data['due_date']) && $data['due_date'] < $hoy) {
            throw new Exception("La fecha de fin no puede ser anterior a hoy.");
        }
        
        $data['assignee_id']     = !empty($data['assignee_id']) ? $data['assignee_id'] : null;
        $data['project_id']      = !empty($data['project_id']) ? $data['project_id'] : null;
        $data['parent_task_id']  = !empty($data['parent_task_id']) ? $data['parent_task_id'] : null;
        $data['start_date']      = !empty($data['start_date']) ? $data['start_date'] : null;
        $data['due_date']        = !empty($data['due_date']) ? $data['due_date'] : null;
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

    // Eliminar
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

    // Obtener subtareas de una tarea
    public function getSubtasks($taskId) {
        $sql = "SELECT t.*, u1.nombre_usuario AS creador_nombre, u2.nombre_usuario AS asignado_nombre, p.name AS proyecto_nombre, tp.title AS parent_title
            FROM {$this->table} t
            LEFT JOIN usuarios u1 ON t.creator_id = u1.id
            LEFT JOIN usuarios u2 ON t.assignee_id = u2.id
            LEFT JOIN projects p ON t.project_id = p.id
            LEFT JOIN tasks tp ON t.parent_task_id = tp.id
            WHERE t.parent_task_id = :taskId
            ORDER BY t.created_at ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":taskId" => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}