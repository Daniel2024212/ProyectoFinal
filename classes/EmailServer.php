<?php

namespace Classes;

// Importamos las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    public function enviar($email, $nombre, $mensaje) {
        
        // 1. Validar formato de email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'resultado' => false,
                'mensaje' => "El email proporcionado no es válido."
            ];
        }

        // 2. Instanciar PHPMailer
        $mail = new PHPMailer(true);

        try {
            // --- CONFIGURACIÓN DEL SERVIDOR (SMTP) ---
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Descomenta para ver errores detallados
            $mail->isSMTP();
            
            // AQUÍ PONES TUS CREDENCIALES (Ejemplo con Mailtrap)
            $mail->Host       = $_ENV['EMAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['EMAIL_USER'] ?? 'TU_USUARIO_MAILTRAP'; 
            $mail->Password   = $_ENV['EMAIL_PASS'] ?? 'TU_PASSWORD_MAILTRAP'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O 'ssl'
            $mail->Port       = $_ENV['EMAIL_PORT'] ?? 2525;

            // --- REMITENTE Y DESTINATARIO ---
            $mail->setFrom('admin@appsalon.com', 'AppSalon Administrador');
            $mail->addAddress($email, $nombre);     // El email que recibimos en la función

            // --- CONTENIDO DEL CORREO (HTML) ---
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Notificación de AppSalon';

            // Diseño básico HTML
            $contenido = "<html>";
            $contenido .= "<body style='font-family: Arial, sans-serif;'>";
            $contenido .= "<h2>Hola, " . $nombre . "</h2>";
            $contenido .= "<p>Tienes un nuevo mensaje del sistema:</p>";
            $contenido .= "<div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px;'>";
            $contenido .= "<p>" . $mensaje . "</p>";
            $contenido .= "</div>";
            $contenido .= "<p style='font-size: 12px; color: #666;'>Este correo fue generado automáticamente.</p>";
            $contenido .= "</body></html>";

            $mail->Body = $contenido;
            
            // Texto plano por si el cliente no soporta HTML
            $mail->AltBody = "Hola $nombre, mensaje: $mensaje";

            // --- ENVIAR ---
            $mail->send();

            return [
                'resultado' => true,
                'mensaje' => "Correo enviado exitosamente a {$email}"
            ];

        } catch (Exception $e) {
            return [
                'resultado' => false,
                'mensaje' => "No se pudo enviar el correo.",
                'error_tecnico' => $mail->ErrorInfo
            ];
        }
    }
}