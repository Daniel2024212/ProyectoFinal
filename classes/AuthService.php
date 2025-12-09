<?php

namespace Classes;

// --- PROTECCIÓN: Importación manual del Modelo ---
// Esto asegura que el servidor encuentre el archivo aunque falle el Autoload
if(file_exists(__DIR__ . '/../models/Usuario.php')) {
    require_once __DIR__ . '/../models/Usuario.php';
}

use Models\Usuario;

class AuthService {

    public function autenticar($email, $password) {
        
        // 1. Verificación de seguridad: ¿Existe la clase Usuario?
        if(!class_exists('Model\Usuario')) {
            return [
                'resultado' => false,
                'error' => 'Error del Servidor: No se cargó el modelo Usuario. Revisa que el archivo models/Usuario.php exista y empiece con mayúscula.'
            ];
        }

        // 2. Buscar usuario por email
        // Usamos una consulta SQL directa para evitar fallos de métodos mágicos
        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";

        try {
            $resultado = Usuario::SQL($query);
        } catch (\Exception $e) {
            return [
                'resultado' => false,
                'error' => 'Error de Base de Datos: ' . $e->getMessage()
            ];
        }

        // 3. Verificar si el usuario existe
        if(empty($resultado)) {
            return [
                'resultado' => false,
                'error' => 'El usuario no existe'
            ];
        }

        // Active Record devuelve un array, tomamos el primer elemento (el usuario)
        $usuario = array_shift($resultado);

        // 4. Verificar Password
        if(password_verify($password, $usuario->password)) {
            
            // Iniciar sesión en el servidor
            if(!isset($_SESSION)) {
                session_start();
            }

            $_SESSION['id'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['login'] = true;

            // Verificar si es admin
            if($usuario->admin === "1") {
                $_SESSION['admin'] = true;
            }

            return [
                'resultado' => true,
                'mensaje' => 'Login Correcto',
                'usuario' => $usuario->nombre,
                'token' => uniqid() // Token simulado para la API
            ];

        } else {
            return [
                'resultado' => false,
                'error' => 'El password es incorrecto'
            ];
        }
    }
}