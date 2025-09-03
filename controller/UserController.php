<?php
require_once __DIR__ . '/../config/database.php';
$con = connection();

session_start();
    if (empty($_POST["correo"]) and empty($_POST["contrasena"])) {
        echo "<div>Los Campos Estan Vacios</div>";
    } else {
        (!empty($_POST["correo"]) and !empty($_POST["contrasena"]));

        $gmail    = $_POST['correo'];
        $password = $_POST['contrasena'];
    }


var_dump($_POST);
?>