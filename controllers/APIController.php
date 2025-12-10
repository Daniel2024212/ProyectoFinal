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
        
        // 1. CARGA MANUAL DE MODELOS (Seguridad)
        if(file_exists(__DIR__ . '/../models/Cita.php')) require_once __DIR__ . '/../models/Cita.php';
        if(file_exists(__DIR__ . '/../models/CitaServicio.php')) require_once __DIR__ . '/../models/CitaServicio.php';

        try {
            // --- INICIO: VALIDACIÓN DE HORARIO Y COLISIONES ---
            
            $fecha = $_POST['fecha'];
            $horaNueva = $_POST['hora'];

            // A. Obtener todas las citas de ese día
            // Usamos una consulta directa para mayor rapidez
            $query = "SELECT hora FROM citas WHERE fecha = '{$fecha}'";
            $citasDelDia = Cita::SQL($query);

            // B. Recorrer las citas existentes para verificar colisiones
            foreach($citasDelDia as $cita) {
                
                // Convertir horas a minutos o timestamp para comparar
                // Ejemplo: 10:30:00 -> timestamp
                $horaExistente = strtotime($cita->hora);
                $horaIntento = strtotime($horaNueva);

                // Calcular la diferencia en segundos y convertir a minutos
                // abs() nos da el valor absoluto (sin importar si es antes o después)
                $diferenciaMinutos = abs($horaExistente - $horaIntento) / 60;

                // C. REGLA DE NEGOCIO:
                // Si la diferencia es menor a 15 minutos, es una colisión.
                // Esto evita citas duplicadas (diferencia 0) y citas empalmadas (ej: 10:00 y 10:10)
                if($diferenciaMinutos < 15) {
                    echo json_encode([
                        'resultado' => false, 
                        'error' => 'Horario no disponible. Debe haber al menos 15 minutos de diferencia con otra cita.'
                    ]);
                    return; // Detenemos la ejecución aquí
                }
            }
            // --- FIN VALIDACIÓN ---


            // 2. Si pasó la validación, guardamos la Cita
            $cita = new Cita($_POST);
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
                $citaServicio = new CitaServicio($args);
                $citaServicio->guardar();
            }

            // ÉXITO
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