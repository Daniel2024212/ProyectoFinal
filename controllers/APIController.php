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
        // Validación de datos obligatorios
        $nombre_cliente = $_POST['nombre_cliente'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $servicios = $_POST['servicios'] ?? '';

        if (empty($nombre_cliente) || empty($fecha) || empty($hora) || empty($servicios)) {
            echo json_encode(['resultado' => false, 'mensaje' => 'Falta datos de servicio, fecha u hora']);
            return;
        }

        // Almacena la cita y devuelve el ID:
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();

        $citaId = $resultado['id'];

        // Almacena los servicios:
        $idServicios = explode(',', $_POST['servicios']);
        foreach ($idServicios as $idServicio) {
            $args = [
                'citaId' => $citaId,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();
        }

        // Retornamos una respuesta:
        // Retornamos la respuesta al frontend
        echo json_encode([
            'resultado' => $resultado,
            'nombre_cliente' => $nombre_cliente
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
