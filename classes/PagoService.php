<?php
namespace Classes;

use Model\Pago;

class PagoService {

    public static function procesarPago(array $datos): array {
        $monto = $datos['monto'] ?? 0;
        
        // Validación simple de negocio
        if($monto <= 0) {
            return ['success' => false, 'error' => 'Monto inválido'];
        }

        $pago = new Pago([
            'usuario_id' => $datos['usuario_id'],
            'cita_id'    => $datos['cita_id'],
            'monto'      => $monto,
            'metodo'     => $datos['metodo'] ?? 'efectivo',
            'estado'     => 'pagado'
        ]);

        $resultado = $pago->guardar();

        return [
            'success' => $resultado['resultado'],
            'referencia' => $pago->id
        ];
    }
}