<?php
namespace Classes;

use Models\Usuario;
use Models\Token;
use Classes\Email;

class AuthService {
    // Registrar un nuevo usuario
    public static function registrarUsuario(array $datos): array {
        $usuario = new Usuario($datos);
        $alertas = $usuario->validar_nueva_cuenta();

        if(empty($alertas)) {
            $existe = $usuario->exite_usuario();
            if($existe->num_rows) {
                return ['success' => false, 'alertas' => Usuario::getAlertas()];
            }

            $usuario->hash_password();
            $token = new Token(); // Generar token
            $token->crear_token();
            
            // Enviar Email (Comunicación con servicio externo de correo)
            $email = new Email($usuario->nombre, $usuario->email, $token->token);
            $email->enviar_confirmacion();

            $resultado = $usuario->guardar();
            $token->usuarioId = $resultado['id'];
            $token->guardar();

            return ['success' => true];
        }
        return ['success' => false, 'alertas' => $alertas];
    }

    // Autenticar usuario (Login)
    public static function autenticar(string $email, string $password): array {
        $usuario = Usuario::where('email', $email);

        if(!$usuario || !$usuario->confirmado) {
            return ['success' => false, 'error' => 'Usuario no existe o no confirmado'];
        }

        if(password_verify($password, $usuario->password)) {
            // Retornamos los datos básicos del usuario para la sesión
            return [
                'success' => true, 
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre . " " . $usuario->apellido,
                    'email' => $usuario->email,
                    'admin' => $usuario->admin
                ]
            ];
        }

        return ['success' => false, 'error' => 'Password incorrecto'];
    }
}