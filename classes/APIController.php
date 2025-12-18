<?php

namespace Controllers;

use Models\Cita;
use Models\CitaServicio;
use Models\Servicio;

class APIController {
    
    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar() {

        // Almacena la cita y devuelve el ID:
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();

        $citaId = $resultado['id'];

        // Almacena la cita y el servicio:
        $idServicios = explode(',', $_POST['servicios']);
        foreach($idServicios as $idServicio) {
            $args = [
                'citaId' => $citaId,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();
        }

        // Retornamos una respuesta:
        echo json_encode(['resultado' => $resultado]);   
    }

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();

            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}