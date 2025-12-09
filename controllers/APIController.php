<?php

namespace Controllers;

// Modelos necesarios
use Models\Cita;
use Models\CitaServicio;
use Models\Servicio;
require_once __DIR__ . '/../classes/AuthService.php';
require_once __DIR__ . '/../classes/CitaService.php';
use Classes\CitaService;
require_once __DIR__ . '/../classes/CatalogoService.php';
use Classes\CatalogoService;
require_once __DIR__ . '/../classes/NotificacionService.php';
require_once __DIR__ . '/../classes/ReporteService.php';

// Importar la clase ReporteService
use Classes\ReporteService;
use Classes\NotificacionService;

// --- CORRECCIÓN IMPORTANTE PARA PANTALLA BLANCA ---
// Cargamos el archivo manualmente porque el autoloader podría no ver la carpeta 'classes'
require_once __DIR__ . '/../classes/AuthService.php';

// Ahora sí lo usamos
use Classes\AuthService; 

class APIController {

    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar() {
        
        // 1. Verificar si la clase Cita existe (Error común de Namespace)
        if(!class_exists('Model\Cita')) {
            echo json_encode(['resultado' => false, 'error' => 'No se encuentra la clase Model\Cita. Revisa el namespace en models/Cita.php']);
            return;
        }

        try {
            // 2. Intentar guardar la Cita
            $cita = new Cita($_POST);
            $resultado = $cita->guardar();

            // Si falla el guardado (ej: error SQL), el resultado suele traer un error
            if(isset($resultado['error'])) {
                 echo json_encode(['resultado' => false, 'error' => 'Error BD Cita: ' . $resultado['error']]);
                 return;
            }

            $id = $resultado['id'];

            // 3. Intentar guardar los Servicios
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
            // CAPTURA DE ERRORES FATALES
            echo json_encode([
                'resultado' => false, 
                'error' => 'Error PHP: ' . $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);
        }
    }

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }

    public static function programadas() {
        // Muestra citas futuras
        $fechaActual = date('Y-m-d');
        $consulta = "SELECT * FROM citas WHERE fecha >= '${fechaActual}' ORDER BY fecha ASC, hora ASC";
        try {
            $citas = Cita::SQL($consulta);
            echo json_encode($citas);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // 1. MICROSERVICIO LOGIN
    public static function auth() {
        $email = $_GET['email'] ?? $_POST['email'] ?? '';
        $password = $_GET['password'] ?? $_POST['password'] ?? '';
        
        $service = new AuthService();
        echo json_encode($service->login($email, $password));
    }

    // 2. NOTIFICACIÓN (REAL - MAIL)
    public static function notificar() {
        $email = $_GET['email'] ?? '';
        $mensaje = $_GET['mensaje'] ?? 'Prueba de servicio real';
        
        $service = new NotificacionService();
        echo json_encode($service->enviarCorreo($email, 'Aviso Importante', $mensaje));
    }

    // 5. MICROSERVICIO REPORTES
    public static function reporte() {
        $service = new ReporteService();
        echo json_encode($service->generarResumenDiario());
    }
    
}