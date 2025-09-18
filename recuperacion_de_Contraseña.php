<?php 

require "C:/wamp64/www/proyecto-1/Gestor-de-Tareas/vendor/autoload.php";
require_once("config/database.php");
session_start();
date_default_timezone_set('America/Bogota'); //esto me srive para que la variable llamda $fecha expiracion tome la hora actual de colombia y no la que tiene configurada en el ini
//lryp hzrc szai oqgd
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = Database::connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email=trim($_POST["email"]);
    $stmt=$conn->prepare("SELECT * FROM usuarios WHERE correo=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado=$stmt->get_result();

    if($resultado->num_rows>0){
        $datosusuario=$resultado->fetch_assoc();

        $token=bin2hex(random_bytes(16));
        $fecha_expiracion=date("Y-m-d H:i:s", strtotime("+1 hour"));
        echo $token;
        echo $fecha_expiracion;
        
        $stmt=$conn->prepare("INSERT INTO contrasenasrecuperar(user_id,token,expires_at) VALUES (?,?,?)");
        $stmt->bind_param("iss", $datosusuario["id"],$token,$fecha_expiracion);
        $stmt->execute();

        $mail= new PHPMailer(true);
        try{
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'daniydiana111@gmail.com'; 
            $mail->Password = 'ehzy ovvm syrl rfoe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true
    ]
];

            $mail->setFrom('daniydiana111@gmail.com', 'Soporte');
            $mail->addAddress($email);

            // $link = "http://localhost/proyecto-1/Gestor-de-Tareas/recuperarcontraseña.php?token=" . $token;
            $link = "http://localhost/proyecto-1/Gestor-de-Tareas/recuperarcontraseña.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject="Recupera tu clave";
            $mail->Body="Entra a este link para obtener tu archivo <link>".$link."</link>";

            $mail->send();
            $_SESSION["Mensajerecuperacion"]="hemos enviado un mensjae de recuperacion";

        } catch(Exception $e){
            $_SESSION['Mensajerecuperacion'] = "Error al enviar el correo: {$mail->ErrorInfo}";
        }
        
    
    }else{
        $_SESSION["errorcorreo"]="El correo no esta registrado";
        header("location:olvidemicontra.php");
    }


   header("location:olvidemicontra.php");

}

?>