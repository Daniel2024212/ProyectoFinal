<?php
namespace Classes;

// Carga manual del modelo para evitar errores en Linux
if(file_exists(__DIR__ . '/../models/Usuario.php')) require_once __DIR__ . '/../models/Usuario.php';

use Models\Usuario;

class AuthService {
    public function login($email, $password) {
        if(!class_exists('Model\Usuario')) return ['error' => 'Error: Modelo Usuario no encontrado'];

        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";
        try {
            $resultado = Usuario::SQL($query);
        } catch (\Exception $e) { return ['error' => $e->getMessage()]; }

        if(empty($resultado)) return ['resultado' => false, 'error' => 'Usuario no encontrado'];

        $usuario = array_shift($resultado);
        if(password_verify($password, $usuario->password)) {
            if(!isset($_SESSION)) session_start();
            $_SESSION['login'] = true;
            $_SESSION['id'] = $usuario->id;
            $_SESSION['email'] = $usuario->email;
            
            return ['resultado' => true, 'token' => uniqid(), 'usuario' => $usuario->nombre];
        }
        return ['resultado' => false, 'error' => 'Password incorrecto'];
    }
}