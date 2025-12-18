<?php
namespace backend;

require_once __DIR__ . '/../classes/AuthService.php';
use Classes\AuthService;

class AuthMicroservice {
    public static function login() {
        // Recibimos datos
        $email = $_GET['email'] ?? $_POST['email'] ?? '';
        $password = $_GET['password'] ?? $_POST['password'] ?? '';
        
        // Usamos la lógica de negocio
        $service = new AuthService();
        $respuesta = $service->login($email, $password);

        // Devolvemos JSON
        echo json_encode($respuesta);
    }
}