<?php
// Mostrar errores en desarrollo (quítalo en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . "/config/database.php";
date_default_timezone_set('America/Bogota');

try {
    if (!class_exists('Databasee')) {
        throw new Exception("Clase Databasee no encontrada. Revisa config/database.php");
    }

    $conn = Databasee::connect();
    if (!$conn) throw new Exception("No se pudo conectar a la base de datos.");

    $sql = "
        SELECT t.id, t.title, t.due_date, t.status, t.recurrence_rule,
               COALESCE(u.nombre_usuario, 'Sin asignar') AS nombre_usuario
        FROM tasks t
        LEFT JOIN usuarios u ON t.assignee_id = u.id
        WHERE t.status != 'done'
          AND t.due_date IS NOT NULL
          AND t.due_date >= NOW()
          AND t.due_date <= DATE_ADD(NOW(), INTERVAL 1 DAY)
        ORDER BY t.due_date ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tareas)) {
        echo "<p class='text-muted'>No tienes tareas próximas a vencer.</p>";
        exit;
    }

    echo "<ul class='list-group'>";
    foreach ($tareas as $t) {
        switch ($t['recurrence_rule']) {
            case 'daily': $mensaje = "Esta tarea es <strong>diaria</strong>."; break;
            case 'weekly': $mensaje = "Esta tarea es <strong>semanal</strong>."; break;
            case 'monthly': $mensaje = "Esta tarea es <strong>mensual</strong>."; break;
            default: $mensaje = "Esta tarea está a punto de vencer.";
        }

        echo "<li class='list-group-item'>";
        echo "<div class='d-flex justify-content-between align-items-start'>";
        echo "<div>";
        echo "<strong>" . htmlspecialchars($t['title']) . "</strong><br>";
        echo "<small>Asignado a: " . htmlspecialchars($t['nombre_usuario']) . "</small>";
        echo "</div>";
        echo "<small>" . date("d/m/Y H:i", strtotime($t['due_date'])) . "</small>";
        echo "</div>";
        echo "<div class='mt-2'><small class='text-warning'>" . $mensaje . "</small></div>";
        echo "</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    http_response_code(500);
    echo "<div class='alert alert-danger'>Error al cargar notificaciones: " 
       . htmlspecialchars($e->getMessage()) . "</div>";
}
