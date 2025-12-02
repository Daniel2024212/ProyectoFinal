<?php

namespace Controllers;

use Models\Cita;          // <--- IMPORTANTE: Esto faltaba y causaba el Error 500
use Models\CitaServicio;
use Models\Servicio;

class APIController {

    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar() {
        // Almacena la Cita y devuelve el ID
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();

        $id = $resultado['id'];

        // Almacena los Servicios con la Cita
        $idServicios = explode(",", $_POST['servicios']);

        foreach($idServicios as $idServicio) {
            $args = [
                'citaId' => $id,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();
        }

        echo json_encode(['resultado' => $resultado]);
    }

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }

    // --- ESTA ES LA FUNCIÓN QUE TE FALTA O TIENE ERROR ---
    public static function programadas() {
        $fecha = $_GET['fecha'] ?? null;

        if(!$fecha) {
            echo json_encode([]);
            return;
        }

        // Consulta SQL segura
        $fecha = filter_var($fecha, FILTER_SANITIZE_STRING);
        $consulta = "SELECT hora FROM citas WHERE fecha = '" . $fecha . "'";

        try {
            // Requiere que tengas el modelo Cita importado arriba
            $citas = Cita::SQL($consulta);
            echo json_encode($citas);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}