<?php
require_once "config/database.php";
date_default_timezone_set('America/Bogota');

$conn = Databasee::connect();

// Obtener tareas pendientes próximas a vencer hoy (24 horas desde ahora)
$stmt = $conn->prepare("
    SELECT t.*, u.nombre_usuario
    FROM tasks t
    JOIN usuarios u ON t.assignee_id = u.id
    WHERE t.status != 'done'
      AND t.due_date IS NOT NULL
      AND t.due_date >= NOW()
      AND t.due_date <= DATE_ADD(NOW(), INTERVAL 1 DAY)
    ORDER BY t.due_date ASC
");
$stmt->execute();
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tareas)) {
    echo "<p>No tienes tareas próximas a vencer.</p>";
} else {
    echo "<ul class='list-group'>";
    foreach ($tareas as $tarea) {
        // Preparar mensaje según recurrencia
        switch ($tarea['recurrence_rule']) {
            case 'daily':
                $mensaje = "Esta tarea es diaria. Recuerda completarla hoy.";
                break;
            case 'weekly':
                $mensaje = "Esta tarea es semanal. Recuerda completarla esta semana.";
                break;
            case 'monthly':
                $mensaje = "Esta tarea es mensual. Recuerda completarla este mes.";
                break;
            case 'none':
                $mensaje = "Esta tarea está a punto de vencer.";
                break;
        }

        // Mostrar la tarea claramente
        echo "<li class='list-group-item'>";
        echo "<strong>Nombre de la tarea:</strong> {$tarea['title']}<br>";
        echo "<strong>Asignado a:</strong> {$tarea['nombre_usuario']}<br>";
        echo "<strong>Vence:</strong> " . date("d/m/Y H:i", strtotime($tarea['due_date'])) . "<br>";
        echo "<em>$mensaje</em>";
        echo "</li>";
    }
    echo "</ul>";
}
