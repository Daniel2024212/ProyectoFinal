<?php
namespace Classes;

class NotificacionService {
    public function enviarCorreo($destinatario, $asunto, $mensaje) {
        
        // 1. Validar Email
        if(!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return ['resultado' => false, 'mensaje' => 'Email inválido'];
        }

        // 2. Configurar Cabeceras para HTML (Profesional)
        // Cambia 'no-reply@tudominio.com' por un correo de tu dominio si tienes uno
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: AppSalon <no-reply@web-salon.mnz.dom.my.id>' . "\r\n";

        // 3. Cuerpo del mensaje
        $contenido = "
        <html>
        <body style='font-family: sans-serif;'>
            <h2 style='color: #0da6f3;'>Notificación AppSalon</h2>
            <p>Hola, tienes un nuevo mensaje:</p>
            <div style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>
                $mensaje
            </div>
            <p><small>Enviado automáticamente.</small></p>
        </body>
        </html>";

        // 4. ENVÍO REAL
        // La arroba @ evita que el script se rompa si el servidor de correo falla
        $enviado = @mail($destinatario, $asunto, $contenido, $headers);

        if($enviado) {
            return ['resultado' => true, 'mensaje' => "Correo enviado a $destinatario"];
        } else {
            return [
                'resultado' => false, 
                'mensaje' => 'El servidor intentó enviar el correo pero falló. Revisa la configuración SMTP de tu hosting.'
            ];
        }
    }
}