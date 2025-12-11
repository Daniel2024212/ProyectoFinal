<?php
namespace Controllers;

use Models\Cita;
use Models\CitaServicio;

class CitaMicroservice {

    // GET: Obtener citas de una fecha
    public static function index() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        // Consulta SQL directa para rapidez
        $sql = "SELECT * FROM citas WHERE fecha = '$fecha'";
        
        try {
            $citas = Cita::SQL($sql);
            echo json_encode($citas);
        } catch (\Exception $e) {
            echo json_encode([]);
        }
    }

    // POST: Guardar una nueva cita
    public static function guardar() {
        // Carga segura de modelos
        if(!class_exists('Model\Cita')) {
            if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        }

        try {
            // 1. Guardar la Cita (Con el nombre personalizado 'cliente')
            $cita = new Cita($_POST);
            $resultado = $cita->guardar();
            $id = $resultado['id'];

            // 2. Guardar los Servicios relacionados
            $idServicios = explode(",", $_POST['servicios']);
            foreach($idServicios as $idServicio) {
                $args = [
                    'citaId' => $id,
                    'servicioId' => $idServicio
                ];
                $citaServicio = new CitaServicio($args);
                $citaServicio->guardar();
            }

            // ÉXITO
            echo json_encode(['resultado' => $resultado]);

        } catch (\Throwable $e) {
            echo json_encode([
                'resultado' => false, 
                'error' => 'Error en Microservicio Citas: ' . $e->getMessage()
            ]);
        }
    }
}