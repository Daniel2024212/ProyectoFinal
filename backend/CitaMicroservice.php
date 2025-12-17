<?php
namespace Backend;

use Models\Cita;
use Models\CitaServicio;

class CitaMicroservice {

    // 1. VER: Obtener citas programadas
    public static function index() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        // Carga de seguridad
        if(!class_exists('Model\Cita')) {
            if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        }

        $sql = "SELECT * FROM citas WHERE fecha = '$fecha'";
        try {
            $citas = Cita::SQL($sql);
            echo json_encode($citas);
        } catch (\Exception $e) {
            echo json_encode([]);
        }
    }

    // 2. GUARDAR: Agendar una nueva cita
    public static function guardar() {
        
        // Cargas de seguridad
        if(!class_exists('Model\Cita')) require_once __DIR__ . '/../models/Cita.php';
        
        if(!class_exists('Model\CitaServicio')) {
            if(file_exists(__DIR__ . '/../models/CitaServicio.php')) require_once __DIR__ . '/../models/CitaServicio.php';
            else if(file_exists(__DIR__ . '/../models/citaservicio.php')) require_once __DIR__ . '/../models/citaservicio.php';
        }

        try {
            // Guardar Cita
            $cita = new Cita($_POST);
            $resultado = $cita->guardar();

            if(!$resultado['resultado']) {
                echo json_encode(['resultado' => false, 'error' => 'Error BD: No se pudo guardar']);
                return;
            }

            // Guardar Servicios
            $id = $resultado['id'];
            $idServicios = explode(",", $_POST['servicios']);
            
            foreach($idServicios as $idServicio) {
                $args = ['citaId' => $id, 'servicioId' => $idServicio];
                $citaServicio = new CitaServicio($args);
                $citaServicio->guardar();
            }

            echo json_encode(['resultado' => $resultado]);

        } catch (\Throwable $e) {
            echo json_encode(['resultado' => false, 'error' => 'Error Backend: ' . $e->getMessage()]);
        }
    }

    // 3. ELIMINAR: Borrar una cita (NUEVO)
    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Carga de seguridad
            if(!class_exists('Model\Cita')) require_once __DIR__ . '/../models/Cita.php';

            try {
                $id = $_POST['id'];
                $cita = Cita::find($id);
                
                if(!$cita) {
                    echo json_encode(['resultado' => false, 'error' => 'Cita no encontrada']);
                    return;
                }

                $resultado = $cita->eliminar();
                echo json_encode(['resultado' => $resultado]);

            } catch (\Throwable $e) {
                echo json_encode(['resultado' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
            }
        }
    }
}