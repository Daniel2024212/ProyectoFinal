<?php

namespace Classes;

// --- AGREGA ESTA LÍNEA PARA SOLUCIONAR EL ERROR ---
// Esto busca el archivo Usuario.php manualmente en la carpeta models
if(file_exists(__DIR__ . '/../models/Usuario.php')) {
    require_once __DIR__ . '/../models/Usuario.php';
}
// ---------------------------------------------------

use Models\Usuario;

class AuthService {

    public function autenticar($email, $password) {
        
        // Verificamos si logramos cargar la clase
        if(!class_exists('Model\Usuario')) {
            // Si entra aquí, significa que NO tienes el archivo Usuario.php
            // o se llama diferente (ej: usuario.php en minúsculas)
            return ['error' => 'Error Crítico: No se encuentra el archivo models/Usuario.php. Revisa que exista y tenga la U mayúscula.'];
        }

        // ... El resto de tu código sigue igual ...
        
        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";

        try {
            $resultado = Usuario::SQL($query);
        } catch (\Exception $e) {
            return ['error' => 'Error BD: ' . $e->getMessage()];
        }

        if(empty($resultado)) {
            return ['resultado' => false, 'error' => 'El usuario no existe'];
        }

        $usuario = array_shift($resultado);

        if(password_verify($password, $usuario->password)) {
            if(!isset($_SESSION)) session_start();
            
            $_SESSION['id'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['login'] = true;
            
            if(isset($usuario->admin) && $usuario->admin === "1") {
                $_SESSION['admin'] = true;
            }

            return [
                'resultado' => true, 
                'mensaje' => 'Login Exitoso',
                'token' => uniqid(),
                'usuario' => $usuario->nombre
            ];
        } else {
            return ['resultado' => false, 'error' => 'Password incorrecto'];
        }
    }
}