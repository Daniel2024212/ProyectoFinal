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
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();
        $id = $resultado['id'];

        $idServicios = explode(",", $_POST['servicios']);
        foreach($idServicios as $idServicio) {
            $args = ['citaId' => $id, 'servicioId' => $idServicio];
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