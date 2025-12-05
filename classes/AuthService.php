<?php

namespace Classes;

use Models\Usuario;

class AuthService {

    public function autenticar($email, $password) {
        
        // Verificamos que el modelo Usuario exista y tenga el método SQL
        if(!class_exists('Model\Usuario')) {
            return ['error' => 'Error Crítico: No se encuentra el Modelo Usuario'];
        }

        // Consulta SQL directa para evitar errores de métodos 'where' no existentes
        // Escapamos los datos para seguridad básica
        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";

        // Ejecutar consulta (Ajusta 'SQL' si tu método se llama 'consultarSQL')
        try {
            $resultado = Usuario::SQL($query);
        } catch (\Exception $e) {
            return ['error' => 'Error de Base de Datos: ' . $e->getMessage()];
        }

        // Si el array está vacío, el usuario no existe
        if(empty($resultado)) {
            return [
                'resultado' => false,
                'error' => 'El usuario no existe'
            ];
        }

        // Obtenemos el primer resultado (Objeto Usuario)
        $usuario = array_shift($resultado);

        // Validar Password
        if(password_verify($password, $usuario->password)) {
            
            // Iniciar sesión
            if(!isset($_SESSION)) {
                session_start();
            }
            
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
            return [
                'resultado' => false,
                'error' => 'Password incorrecto'
            ];
        }
    }
}