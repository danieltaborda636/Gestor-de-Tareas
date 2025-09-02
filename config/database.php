<?php
function connection(){
    $host = "localhost";
    $user = "root";
    $pass = "";

    $database = "gestortareas";

    $connect = mysqli_connect($host, $user, $pass);

    mysqli_select_db($connect,$database);

    return $connect;


}