<?php

class Database {
        public static function connect(){
            if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
            if (!defined('DB_USERNAME')) define("DB_USERNAME", "root");
            if (!defined('DB_PASSWORD')) define("DB_PASSWORD", '');
            if (!defined('DB_NAME')) define('DB_NAME', 'gestortareas');

            $conexion = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

            if (!$conexion) {
                die("Error de conexión: " . mysqli_connect_error());
            }
            return $conexion;
        }
    }