<?php
namespace Controllers\API;
use MVC\Router;
use Model\Valoracion;

class ValoracionesController {
    public static function crear() {
        $input = json_decode(file_get_contents('php://input'), true);

        $usuarioId = $input['usuario_id'] ?? null;
        $citaId    = $input['cita_id'] ?? null;
        $estrellas = $input['estrellas'] ?? null;
        $coment    = $input['comentario'] ?? '';

        if(!$usuarioId || !$citaId || !$estrellas) {
            echo json_encode(['success'=>false,'error'=>'Datos incompletos']);
            return;
        }

        $valor = new Valoracion([
            'usuario_id' => $usuarioId,
            'cita_id'    => $citaId,
            'estrellas'  => $estrellas,
            'comentario' => $coment
        ]);
        $valor->guardar();

        echo json_encode(['success'=>true,'valoracion_id'=>$valor->id]);
    }
}
