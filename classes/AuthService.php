<?php

namespace Classes;

use Models\Usuario; // Asegúrate de tener tu modelo Usuario

class AuthService {

    public function autenticar($email, $password) {
        // 1. Buscar usuario por email
        $usuario = Usuario::where('email', $email);

        if(!$usuario) {
            return ['error' => 'El usuario no existe'];
        }

        // 2. Verificar si está confirmado
        if(!$usuario->confirmado) {
             return ['error' => 'Tu cuenta no ha sido confirmada'];
        }

        // 3. Verificar password
        if(password_verify($password, $usuario->password)) {
            // Login correcto: Devolvemos datos básicos (sin el password)
            return [
                'auth' => true,
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'token' => uniqid() // Simulación de token
            ];
        } else {
            return ['error' => 'Password incorrecto'];
        }
    }
}