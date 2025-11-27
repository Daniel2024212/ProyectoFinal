<?php

namespace Controllers;

use Models\Cita;
use Models\CitaServicio;
use Models\Servicio;

class APIController
{
    public static function index()
    {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar()
    {
        // 1. Almacena la Cita
        $cita = new Cita($_POST);
        $resultado = $cita->guardar(); // Devuelve ['resultado'=>true, 'id'=>123]

        $citaId = $resultado['id'];

        // 2. Almacena los Servicios
        $idServicios = explode(',', $_POST['servicios']);
        foreach ($idServicios as $idServicio) {
            $args = [
                'citaId' => $citaId,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();
        }

        // CORRECCIÓN IMPORTANTE:
        // Devolvemos el array $resultado directamente para que JS pueda leer 'id' y 'resultado' sin problemas.
        echo json_encode([
            'resultado' => $resultado['resultado'],
            'id' => $resultado['id'] // Esto es lo que faltaba para que funcione el pago
        ]);
    }

    public static function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}