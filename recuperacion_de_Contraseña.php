<?php
session_start();
date_default_timezone_set('America/Bogota'); // Hora local

require_once __DIR__ . "/config/database.php";
require "C:/wamp64/www/proyecto-1/Gestor-de-Tareas/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = Databasee::connect(); // PDO

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $_SESSION["errorcorreo"] = "Por favor ingresa un correo.";
        header("Location: olvidemicontra.php");
        exit;
    }

    // Buscar usuario
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->execute([':correo' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION["errorcorreo"] = "El correo no está registrado";
        header("Location: olvidemicontra.php");
        exit;
    }

    // Crear token y expiración
    $token = bin2hex(random_bytes(16));
    $fecha_expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Guardar token en la base
    $stmt = $conn->prepare("INSERT INTO contrasenasrecuperar (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->execute([
        ':user_id' => $usuario['id'],
        ':token' => $token,
        ':expires_at' => $fecha_expiracion
    ]);

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'daniydiana111@gmail.com'; // Cambia por tu correo
        $mail->Password = 'ehzy ovvm syrl rfoe'; // Contraseña de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('tucorreo@gmail.com', 'Soporte');
        $mail->addAddress($email);

        $link = "http://localhost/proyecto-1/Gestor-de-Tareas/recuperarcontraseña.php?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = "Recupera tu contraseña";
        $mail->Body = "Haz clic en este enlace para restablecer tu contraseña: <a href='$link'>$link</a>";

        $mail->send();

        $_SESSION["Mensajerecuperacion"] = "Hemos enviado un mensaje de recuperación a tu correo.";
    } catch (Exception $e) {
        $_SESSION["errorcorreo"] = "Error al enviar el correo: {$mail->ErrorInfo}";
    }

    header("Location: olvidemicontra.php");
    exit;
}
