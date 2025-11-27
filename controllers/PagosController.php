<?php
namespace Controllers\API;
use MVC\Router;
use Model\Pago;
use Models\Cita;     // Nota: Namespace plural según tu archivo Cita.php
use Models\Usuario;
use Classes\PagoService;

class PagosController {

    public static function crear() {
        // 1. Leer datos JSON
        $input = json_decode(file_get_contents('php://input'), true);

        // 2. Validar datos mínimos
        $usuarioId = $input['usuario_id'] ?? null;
        $citaId    = $input['cita_id'] ?? null;
        $monto     = $input['monto'] ?? 0;
        $metodo    = $input['metodo'] ?? 'efectivo';

        if(!$usuarioId || !$citaId || $monto <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos o incompletos']);
            return;
        }

        // 3. Validar existencia (Integridad Referencial)
        $cita = Cita::find($citaId);
        if(!$cita) {
            echo json_encode(['success' => false, 'error' => 'La cita no existe en el sistema']);
            return;
        }

        // 4. Lógica de Pasarela de Pagos (Simulación)
        $estado = 'pendiente';
        $referencia = uniqid('PAGO_');

        if($metodo === 'tarjeta' || $metodo === 'paypal') {
            // Simulamos conexión con banco (90% éxito)
            $aprobado = rand(1, 100) > 10; 
            
            if(!$aprobado) {
                echo json_encode(['success' => false, 'error' => 'Transacción rechazada por el banco']);
                return;
            }
            
            $estado = 'pagado';
            $referencia = 'TXN_' . strtoupper(bin2hex(random_bytes(4)));
        } else {
            // Efectivo
            $estado = 'pendiente_pago';
        }

        // 5. Guardar en Base de Datos
        $pago = new Pago([
            'cita_id'    => $citaId,
            'usuario_id' => $usuarioId,
            'monto'      => $monto,
            'metodo'     => $metodo,
            'estado'     => $estado,
            'referencia' => $referencia
        ]);

        $resultado = $pago->guardar();

        echo json_encode([
            'success' => $resultado['resultado'],
            'referencia' => $referencia,
            'mensaje' => $resultado['resultado'] ? 'Pago registrado correctamente' : 'Error al guardar en BD'
        ]);
    }
}