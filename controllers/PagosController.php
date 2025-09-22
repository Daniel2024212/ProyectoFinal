<?php
namespace Controllers\API;
use MVC\Router;
use Model\Pago;

class PagosController {
    public static function crear() {
        $input = json_decode(file_get_contents('php://input'), true);

        $usuarioId = $input['usuario_id'] ?? null;
        $citaId    = $input['cita_id'] ?? null;
        $monto     = $input['monto'] ?? 0;
        $metodo    = $input['metodo'] ?? '';

        if(!$usuarioId || $monto <= 0 || !$metodo) {
            echo json_encode(['success'=>false,'error'=>'Datos incompletos']);
            return;
        }

        $pago = new Pago([
            'usuario_id' => $usuarioId,
            'cita_id'    => $citaId,
            'metodo'     => $metodo,
            'monto'      => $monto
        ]);
        $pago->guardar();

        echo json_encode(['success'=>true,'pago_id'=>$pago->id]);
    }
}
