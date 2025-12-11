<?php
namespace Backend; // <--- Namespace nuevo

require_once __DIR__ . '/../classes/AuthService.php';
use Classes\AuthService;

class AuthMicroservice {
    public static function login() {
        $email = $_GET['email'] ?? $_POST['email'] ?? '';
        $password = $_GET['password'] ?? $_POST['password'] ?? '';
        
        $service = new AuthService();
        echo json_encode($service->login($email, $password));
    }
}