<?php
namespace Controllers\API;
use MVC\Router;
use Model\Pago;
use Models\Cita;     // Nota: Namespace plural según tu archivo Cita.php
use Models\Usuario;
use Classes\PagoService;

class PagosController {

    public static function crear() {
        // Leer el body JSON
        $input = json_decode(file_get_contents('php://input'), true);

        $usuarioId = $input['usuario_id'] ?? null;
        $citaId    = $input['cita_id'] ?? null;
        $monto     = $input['monto'] ?? 0;
        $metodo    = $input['metodo'] ?? 'efectivo';

        // 1. Validaciones básicas
        if(!$usuarioId || !$citaId || $monto <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos o monto inválido']);
            return;
        }

        // 2. Integridad Referencial (Verificar que existen en BD)
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

        // 3. Procesar cobro (Lógica de Negocio)
        $estadoPago = 'pendiente';
        $referencia = uniqid('REF_');

        if ($metodo === 'tarjeta' || $metodo === 'paypal') {
            // Simulamos la pasarela aquí mismo en el controlador
            $resultadoBanco = self::simularPasarelaPago($monto);
            
            if (!$resultadoBanco['aprobado']) {
                echo json_encode(['success' => false, 'error' => 'Pago rechazado: ' . $resultadoBanco['mensaje']]);
                return;
            }
            $estadoPago = 'completado';
            $referencia = $resultadoBanco['transaccion_id'];
        } else {
            $estadoPago = 'en_caja'; // Efectivo
        }

        // 4. Guardar en Base de Datos
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
                'estado' => $estadoPago,
                'referencia' => $referencia
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar el pago']);
        }
    }

    // Método privado auxiliar para simular el banco
    private static function simularPasarelaPago($monto) {
        $exito = rand(1, 100) > 10; // 90% de probabilidad de éxito
        if ($exito) {
            return ['aprobado' => true, 'transaccion_id' => 'TXN_' . bin2hex(random_bytes(8))];
        }
        return ['aprobado' => false, 'mensaje' => 'Fondos insuficientes'];
    }
}