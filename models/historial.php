<?php
// models/Historial.php
require_once __DIR__ . "/../config/Database.php";

class Historial {
    private $conn;
    private $table = "historial";

    public function __construct($db) {
        $this->conn = $db; // Objeto PDO
    }

    // Registrar acción (alias para add)
    public function registrar($usuario_id, $accion, $detalle = null) {
        return $this->add($usuario_id, $accion, $detalle);
    }

    // Guardar una acción en el historial
    public function add($usuario_id, $accion, $detalle = null) {
        $sql = "INSERT INTO {$this->table} (usuario_id, accion, detalle, fecha) 
                VALUES (:usuario_id, :accion, :detalle, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ":usuario_id" => $usuario_id,
            ":accion"     => $accion,
            ":detalle"    => $detalle
        ]);
    }

    // Obtener historial de un usuario
    public function getByUser($usuario_id) {
        $sql = "SELECT id, accion, detalle, fecha 
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":usuario_id" => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
