<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Historial.php'; // Asegúrate de que exista este archivo

// Validar sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['user'];

// Conexión a la base de datos
$database = new Databasee();
$db = $database->getConnection();

// Instanciar modelo Historial
$historialModel = new Historial($db);

// Obtener historial de acciones del usuario
try {
    $historial = $historialModel->getByUser($usuario['id']);
} catch (PDOException $e) {
    error_log("Error historial.php: " . $e->getMessage());
    $historial = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Acciones - Taskify</title>
    <link rel="stylesheet" href="http://localhost/proyecto-1/Gestor-de-Tareas/css/pruevas.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f0f0f0;
        }
        .overview-section {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="http://localhost/proyecto-1/Gestor-de-Tareas/assets/uploads/logo.png" alt="Logo de Taskify"> 
                <h1>TASKIFY</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="inicio.php"><i class="icon-dashboard"></i> Panel de Control</a></li>
                    <li><a href="vistaTareas.php"><i class="icon-mytasks"></i> Mis Tareas</a></li>
                    <li><a href="vistaProyectos.php"><i class="icon-projects"></i> Proyectos</a></li>
                    <li><a href="vistaEtiqueta.php"><i class="icon-tags"></i> Etiquetas</a></li>
                    <li class="active"><a href="historial.php"><i class="icon-history"></i> Historial</a></li>
                    <li><a href="#"><i class="icon-admin"></i> Administración</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="user-profile">
                    <img src="<?= htmlspecialchars($usuario['foto_perfil'] ?? 'assets/uploads/default.jpeg') ?>" alt="Avatar de Usuario">
                    <span><?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?></span>
                    <form action="logout.php" method="POST">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </header>

            <section class="overview-section">
                <h2>Historial de Acciones</h2>
                <?php if (!empty($historial)) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Acción</th>
                                <th>Detalle</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $item) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['accion'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['detalle'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($item['fecha'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No se han registrado acciones aún.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
