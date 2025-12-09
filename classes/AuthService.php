<?php
namespace Classes;

// Carga manual del modelo para asegurar conexión en Linux
if(file_exists(__DIR__ . '/../models/Usuario.php')) {
    require_once __DIR__ . '/../models/Usuario.php';
} elseif(file_exists(__DIR__ . '/../Models/Usuario.php')) {
    require_once __DIR__ . '/../Models/Usuario.php';
}

use Models\Usuario;

class AuthService {
    public function login($email, $password) {
        // 1. Verificar si el modelo cargó correctamente
        if(!class_exists('Model\Usuario')) {
            return ['resultado' => false, 'error' => 'Error crítico: No se encuentra el Modelo Usuario'];
        }

        // 2. Buscar usuario en la BD real
        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";
        
        try {
            $resultado = Usuario::SQL($query);
        } catch (\Exception $e) {
            return ['resultado' => false, 'error' => 'Error BD: ' . $e->getMessage()];
        }

        if(empty($resultado)) {
            return ['resultado' => false, 'error' => 'El usuario no existe'];
        }

        $usuario = array_shift($resultado);

        // 3. Verificar Password Real (Hasheado)
        if(password_verify($password, $usuario->password)) {
            if(!isset($_SESSION)) session_start();
            $_SESSION['login'] = true;
            $_SESSION['id'] = $usuario->id;
            $_SESSION['email'] = $usuario->email;
            
            return [
                'resultado' => true, 
                'mensaje' => 'Login Exitoso', 
                'token' => uniqid(), 
                'usuario' => $usuario->nombre
            ];
        }

        return ['resultado' => false, 'error' => 'Password incorrecto'];
    }
}