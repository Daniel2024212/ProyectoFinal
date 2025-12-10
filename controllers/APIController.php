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
        
        // 1. CARGA MANUAL DE MODELOS
        if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        if(file_exists(__DIR__ . '/../models/CitaServicio.php')) require_once __DIR__ . '/../models/CitaServicio.php';

        try {
            // --- VALIDACIÓN INTELIGENTE DE HORARIO ---
            $fecha = $_POST['fecha'];
            $horaNueva = $_POST['hora'];
            
            // Convertimos la hora ingresada a timestamp (segundos)
            $timestampNuevo = strtotime($horaNueva);

            // Consultar citas del día
            $query = "SELECT hora FROM citas WHERE fecha = '{$fecha}'";
            $citasDelDia = \Models\Cita::SQL($query);

            foreach($citasDelDia as $cita) {
                $horaOcupada = $cita->hora; // Ejemplo: "10:00:00"
                $timestampOcupado = strtotime($horaOcupada);

                // Diferencia en minutos (absoluta)
                $diferencia = abs($timestampOcupado - $timestampNuevo) / 60;

                // Si hay menos de 15 minutos de diferencia (COLISIÓN)
                if($diferencia < 15) {
                    
                    // Calculamos la hora sugerida (Hora Ocupada + 15 minutos)
                    // Nota: strtotime suma segundos, así que 15 min * 60 seg
                    $sugerencia = date('H:i', $timestampOcupado + (15 * 60));
                    
                    // Formato limpio para el mensaje (quitamos los segundos extra 00:00:00 -> 00:00)
                    $horaOcupadaLegible = date('H:i', $timestampOcupado);

                    echo json_encode([
                        'resultado' => false, 
                        'error' => "Horario no disponible. Choca con la cita de las {$horaOcupadaLegible}. Por favor, intenta a las {$sugerencia} o después."
                    ]);
                    return; // Detener proceso
                }
            }
            // --- FIN VALIDACIÓN ---

            // 2. Si pasa la validación, guardamos
            $cita = new \Models\Cita($_POST);
            $resultado = $cita->guardar();

            if(!isset($resultado['resultado']) || !$resultado['resultado']) {
                 throw new \Exception("Error al insertar en la BD.");
            }

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

            echo json_encode(['resultado' => $resultado]);

        } catch (\Throwable $e) {
            echo json_encode([
                'resultado' => false, 
                'error' => 'Error del Sistema: ' . $e->getMessage()
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