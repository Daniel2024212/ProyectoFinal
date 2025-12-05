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
        // Opción segura usando SQL directo si 'where' falla
        $query = "SELECT * FROM usuarios WHERE email = '" . $email . "' LIMIT 1";
        
        // Usamos la función consultarSQL o SQL que tenga tu modelo
        // Nota: Ajusta 'SQL' o 'consultarSQL' según como se llame en tu proyecto
        $resultado = Usuario::SQL($query); 

        // Si no encontró nada, el array está vacío
        if(empty($resultado)) {
            return ['resultado' => false, 'error' => 'El usuario no existe'];
        }

        // Active Record devuelve un array de objetos, tomamos el primero
        $usuario = array_shift($resultado);

        // Verificar password
        if(password_verify($password, $usuario->password)) {
            // ... (resto del código de sesión igual que antes) ...
             if(!isset($_SESSION)) session_start();
             $_SESSION['login'] = true;
             // ...
            return ['resultado' => true, 'token' => uniqid()];
        } else {
            return ['resultado' => false, 'error' => 'Password incorrecto'];
        }
    }
}