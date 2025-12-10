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
        
        // 1. Carga manual de modelos (Seguridad Linux)
        if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        if(file_exists(__DIR__ . '/../models/CitaServicio.php')) require_once __DIR__ . '/../models/CitaServicio.php';

        try {
            // --- INICIO: VALIDACIÓN DE 15 MINUTOS ---
            $fecha = $_POST['fecha'];
            $horaNueva = $_POST['hora'];
            
            // 1. Consultar todas las citas de ese día
            $query = "SELECT hora FROM citas WHERE fecha = '{$fecha}'";
            $citas = \Models\Cita::SQL($query);

            foreach($citas as $cita) {
                // Convertir horas a minutos para comparar
                // Ej: "10:30:00" -> Timestamp
                $horaExistente = strtotime($cita->hora);
                $horaIntento = strtotime($horaNueva);

                // Diferencia en minutos (valor absoluto)
                $diferencia = abs($horaExistente - $horaIntento) / 60;

                // Si la diferencia es menor a 15 minutos, bloqueamos
                if($diferencia < 15) {
                    echo json_encode([
                        'resultado' => false, 
                        'error' => "Horario no disponible. Ya existe una cita a las " . date('H:i', $horaExistente) . ". Debe haber 15 mins de diferencia."
                    ]);
                    return; // ¡IMPORTANTE! Detenemos el guardado aquí
                }
            }
            // --- FIN VALIDACIÓN ---


            // 2. Guardar Cita si pasó la validación
            $cita = new \Models\Cita($_POST);
            $resultado = $cita->guardar();
            $id = $resultado['id'];

            // 3. Guardar Servicios
            $idServicios = explode(",", $_POST['servicios']);
            foreach($idServicios as $idServicio) {
                $args = [
                    'citaId' => $id,
                    'servicioId' => $idServicio
                ];
                $citaServicio = new \Models\CitaServicio($args);
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