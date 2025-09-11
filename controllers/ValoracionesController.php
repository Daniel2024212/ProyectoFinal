<?php

namespace Controllers;

use MVC\Router;
use Model\Valoracion;

class ValoracionesController {

    public static function crear(Router $router) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $valoracion = new Valoracion($_POST);

            // Validar campos
            if(
                empty($valoracion->usuario_id) ||
                empty($valoracion->servicio_id) ||
                empty($valoracion->estrellas)
            ) {
                echo json_encode(['success'=>false,'error'=>'Campos requeridos']);
                return;
            }

            $valoracion->guardar();
            echo json_encode(['success'=>true,'id'=>$valoracion->id]);
        }
    }

    public static function listar(Router $router) {
        // Filtrar por servicio usando el método where existente
        $servicio_id = $_GET['servicio_id'] ?? null;

        if(!$servicio_id) {
            echo json_encode(['success'=>false,'error'=>'servicio_id requerido']);
            return;
        }

        // where solo admite un campo
        $valoraciones = Valoracion::where('servicio_id', $servicio_id);
        echo json_encode($valoraciones);
    }
}
