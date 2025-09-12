<?php
// Maneja login, registro y logout
require_once __DIR__ . "/../models/User.php";

class AuthController {
    public function login($email, $password) {
        // TODO: validar usuario con User::findByEmail
    }

    public function register($name, $email, $password) {
        // TODO: insertar usuario con User::create
    }

    public function logout() {
        session_destroy();
        header("Location: ../views/auth/login.php");
    }
}
