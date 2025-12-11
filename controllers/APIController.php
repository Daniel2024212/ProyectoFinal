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
        
        // 1. Carga manual de modelos (Anti-error Linux)
        if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        if(file_exists(__DIR__ . '/../models/CitaServicio.php')) require_once __DIR__ . '/../models/CitaServicio.php';

        try {
            // --- VALIDACIÓN DE 15 MINUTOS Y DISPONIBILIDAD ---
            $fecha = $_POST['fecha'];
            $horaNueva = $_POST['hora'];
            
            // Consultar citas existentes de ese día
            $query = "SELECT hora FROM citas WHERE fecha = '{$fecha}'";
            $citas = \Models\Cita::SQL($query);

            foreach($citas as $cita) {
                $horaExistente = strtotime($cita->hora);
                $horaIntento = strtotime($horaNueva);

                // Diferencia en minutos (valor absoluto)
                $diferencia = abs($horaExistente - $horaIntento) / 60;

                // REGLA: Si hay menos de 15 minutos de diferencia
                if($diferencia < 15) {
                    $horaOcupada = date('H:i', $horaExistente);
                    
                    // MENSAJE DE ERROR CLARO Y ESPECÍFICO
                    echo json_encode([
                        'resultado' => false, 
                        'error' => "Lo sentimos, la hora {$horaOcupada} ya está ocupada (o muy cerca). Por favor selecciona una hora con al menos 15 minutos de diferencia."
                    ]);
                    return; // Detenemos el guardado
                }
            }
            // --- FIN VALIDACIÓN ---

            // 2. Guardar Cita
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

            // ÉXITO
            echo json_encode(['resultado' => $resultado]);

        } catch (\Throwable $e) {
            // ERROR DE SERVIDOR
            echo json_encode(['resultado' => false, 'error' => 'Ocurrió un error en el servidor: ' . $e->getMessage()]);
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