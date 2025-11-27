<?php
namespace Controllers\API;
use MVC\Router;
use Model\Pago;
use Models\Cita;     // Nota: Namespace plural según tu archivo Cita.php
use Models\Usuario;
use Classes\PagoService;

class PagosController {

    public static function crear() {
        // 1. Leer el input JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $usuarioId = $input['usuario_id'] ?? null;
        $citaId    = $input['cita_id'] ?? null;
        $monto     = $input['monto'] ?? 0;
        $metodo    = $input['metodo'] ?? 'efectivo';

        // 2. Validación básica de datos
        if(!$usuarioId || !$citaId || $monto <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos o monto inválido']);
            return;
        }

        // 3. Integridad: Verificar que la Cita y el Usuario existan en la BD
        // Nota: Asumimos que existen los modelos Cita y Usuario con método find
        $cita = Cita::find($citaId);
        if(!$cita) {
            echo json_encode(['success' => false, 'error' => 'La cita no existe']);
            return;
        }

        $usuario = Usuario::find($usuarioId);
        if(!$usuario) {
            echo json_encode(['success' => false, 'error' => 'El usuario no existe']);
            return;
        }

        // 4. Lógica de Negocio: Procesar el cobro
        $estadoPago = 'pendiente';
        $referencia = uniqid('REF_'); // Generar referencia única

        if ($metodo === 'tarjeta' || $metodo === 'paypal') {
            // Simulamos la conexión con el banco
            $resultadoBanco = self::simularPasarelaPago($monto);
            
            if (!$resultadoBanco['aprobado']) {
                echo json_encode(['success' => false, 'error' => 'Pago rechazado: ' . $resultadoBanco['mensaje']]);
                return;
            }
            $estadoPago = 'completado';
            $referencia = $resultadoBanco['transaccion_id'];
        } else {
            $estadoPago = 'en_caja'; // Pago en efectivo
        }

        // 5. Persistencia: Guardar el Pago
        $pago = new Pago([
            'usuario_id' => $usuarioId,
            'cita_id'    => $citaId,
            'monto'      => $monto,
            'metodo'     => $metodo,
            'estado'     => $estadoPago,
            'referencia' => $referencia
        ]);

        $resultado = $pago->guardar();

        if ($resultado['resultado']) {
            echo json_encode([
                'success' => true, 
                'pago_id' => $resultado['id'],
                'estado'  => $estadoPago,
                'referencia' => $referencia
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error interno al guardar el pago']);
        }
    }

    /**
     * Método auxiliar privado para simular una API externa (Stripe/PayPal)
     */
    private static function simularPasarelaPago($monto) {
        // Simulación: 90% de probabilidad de éxito
        $exito = rand(1, 100) > 10; 
        
        if ($exito) {
            return ['aprobado' => true, 'transaccion_id' => 'TXN_' . bin2hex(random_bytes(8))];
        }
        return ['aprobado' => false, 'mensaje' => 'Fondos insuficientes o tarjeta rechazada'];
    }
}