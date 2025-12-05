<?php

namespace Classes;

use Models\Usuario;

class AuthService {

    /**
     * Verifica las credenciales del usuario
     * @param string $email
     * @param string $password
     * @return array
     */
    public function autenticar($email, $password) {
        
        // 1. Buscar el usuario por email en la base de datos
        // Usamos el método 'where' de tu modelo ActiveRecord
        $usuario = Usuario::where('email', $email);

        // 2. Validar si el usuario existe
        if(!$usuario) {
            return [
                'resultado' => false,
                'error' => 'El usuario no existe'
            ];
        }

        // 3. Validar si el usuario ha confirmado su cuenta (opcional)
        // Si en tu BD la columna es 'confirmado' y usa 1 o 0
        if($usuario->confirmado === "0") {
             return [
                 'resultado' => false,
                 'error' => 'Tu cuenta no ha sido confirmada aún'
             ];
        }

        // 4. Verificar el password
        // password_verify compara el texto plano con el hash de la BD
        if(password_verify($password, $usuario->password)) {
            
            // Inicio de sesión exitoso
            // Iniciamos la sesión PHP aquí para guardar los datos
            if(!isset($_SESSION)) {
                session_start();
            }
            
            $_SESSION['id'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['login'] = true;

            // Si es admin (asumiendo que tienes campo 'admin' en la BD)
            if($usuario->admin === "1") {
                $_SESSION['admin'] = true;
            }

            return [
                'resultado' => true,
                'mensaje' => 'Autenticación correcta',
                'token' => uniqid(), // Simulación de token para API
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre
                ]
            ];

        } else {
            return [
                'resultado' => false,
                'error' => 'El password es incorrecto'
            ];
        }
    }
}