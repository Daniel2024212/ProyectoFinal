<?php
namespace Classes;

class NotificacionService {
    public function enviarAviso($email, $mensaje) {
        // Validación básica
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['resultado' => false, 'mensaje' => 'Email inválido'];
        }

        // Aquí iría la lógica de PHPMailer. Simulamos éxito:
        return [
            'resultado' => true,
            'destino' => $email,
            'tipo' => 'Notificación Sistema',
            'contenido' => $mensaje,
            'status' => 'Enviado'
        ];
    }
}