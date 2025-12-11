<?php

namespace Controllers;

use Models\Cita;
use Models\CitaServicio;
use Models\Servicio;

// Importar Microservicios (Classes)
require_once __DIR__ . '/../classes/AuthService.php';
require_once __DIR__ . '/../classes/NotificacionService.php';
require_once __DIR__ . '/../classes/CitaService.php';
require_once __DIR__ . '/../classes/CatalogoService.php';
require_once __DIR__ . '/../classes/ReporteService.php';

use Classes\AuthService;
use Classes\NotificacionService;
use Classes\CitaService;
use Classes\CatalogoService;
use Classes\ReporteService;

class APIController {

    public static function index() {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    // --- FUNCIÓN PRINCIPAL DE GUARDADO CON VALIDACIÓN ---
    public static function guardar() {
        
        // Carga segura del modelo
        if(!class_exists('Model\Cita')) {
            if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        }

        try {
            // --- YA NO HAY VALIDACIÓN DE 15 MINUTOS ---
            // Guardamos directamente lo que el usuario envió

            // 1. Guardar la Cita
            $cita = new Cita($_POST);
            $resultado = $cita->guardar();
            $id = $resultado['id'];

            // 2. Guardar los Servicios
            $idServicios = explode(",", $_POST['servicios']);
            foreach($idServicios as $idServicio) {
                $args = [
                    'citaId' => $id,
                    'servicioId' => $idServicio
                ];
                $citaServicio = new CitaServicio($args);
                $citaServicio->guardar();
            }

            // Responder Éxito
            echo json_encode(['resultado' => $resultado]);

        } catch (\Throwable $e) {
            echo json_encode(['resultado' => false, 'error' => 'Error del Servidor: ' . $e->getMessage()]);
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

    // --- MICROSERVICIOS ---
    public static function auth() {
        $service = new AuthService();
        echo json_encode($service->login($_GET['email']??'', $_GET['password']??''));
    }

    public static function notificar() {
        $service = new NotificacionService();
        echo json_encode($service->enviarCorreo($_GET['email']??'', 'Aviso', $_GET['mensaje']??''));
    }

    public static function citas() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $sql = "SELECT * FROM citas WHERE fecha = '$fecha'";
        echo json_encode(Cita::SQL($sql));
    }
    
    public static function catalogo() { $s = new CatalogoService(); echo json_encode($s->listarServicios()); }
    public static function reporte() { $s = new ReporteService(); echo json_encode($s->generarResumenDiario()); }
}