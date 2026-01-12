<?php

namespace Controllers;

use MVC\Router;
use Model\Valoracion;

class ValoracionController {

    public static function crear(Router $router) {
        // Verificar sesión
        if(!isset($_SESSION)) session_start();
        
        // Proteger ruta
        if(!isset($_SESSION['login'])) {
            header('Location: /');
        }

        $alertas = [];
        $valoracion = new Valoracion;
        
        // Obtener ID de la cita de la URL
        $citaId = $_GET['id'] ?? null;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $valoracion->sincronizar($_POST);
            $valoracion->usuarioId = $_SESSION['id'];
            $valoracion->citaId = $citaId;

            $alertas = $valoracion->validar();

            if(empty($alertas)) {
                $resultado = $valoracion->guardar();
                if($resultado) {
                    // Redirigir a 'mis citas' tras guardar
                    header('Location: /cita'); 
                }
            }
        }

        $router->render('valoraciones/crear', [
            'alertas' => $alertas,
            'valoracion' => $valoracion,
            'citaId' => $citaId
        ]);
    }
}