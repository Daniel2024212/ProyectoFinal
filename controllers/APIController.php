<?php

namespace Controllers;

// Modelos necesarios
use Models\Cita;
use Models\CitaServicio;
use Models\Servicio;
require_once __DIR__ . '/../classes/EmailService.php'; 
use Classes\EmailService;

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

    // --- NUEVO MÉTODO DE LOGIN ---
    public static function login() {
        
        // Aceptamos GET (para probar en navegador) y POST (para la App)
        if($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
            
            // Datos por defecto para probar si entras por URL sin datos
            // CAMBIA ESTOS DATOS por un usuario real de tu BD para que de TRUE
            $email = $_POST['email'] ?? $_GET['email'] ?? 'correo@correo.com'; 
            $password = $_POST['password'] ?? $_GET['password'] ?? '123456';

            $auth = new AuthService();
            $respuesta = $auth->autenticar($email, $password);

            echo json_encode($respuesta);
        }
    }
}