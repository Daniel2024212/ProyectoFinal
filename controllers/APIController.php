<?php

namespace Controllers;

use Models\Cita;          // <--- IMPORTANTE: Esto faltaba y causaba el Error 500
use Models\CitaServicio;
use Models\Servicio;
use Classes\AuthService;

class APIController{
    public static function index()
    {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar()
    {
        // Almacena la Cita y devuelve el ID
        $cita = new Cita($_POST);
        $resultado = $cita->guardar();

        $id = $resultado['id'];

        // Almacena los Servicios con la Cita
        $idServicios = explode(",", $_POST['servicios']);

        foreach ($idServicios as $idServicio) {
            $args = [
                'citaId' => $id,
                'servicioId' => $idServicio
            ];
            $citaServicio = new CitaServicio($args);
            $citaServicio->guardar();
        }

        echo json_encode(['resultado' => $resultado]);
    }

    public static function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $cita = Cita::find($id);
            $cita->eliminar();
            header('Location:' . $_SERVER['HTTP_REFERER']);
        }
    }

    public static function programadas()
    {
        // Obtenemos la fecha actual del servidor en formato YYYY-MM-DD
        $fechaActual = date('Y-m-d');

        // Consulta: Traer todas las citas cuya fecha sea igual o mayor a hoy
        // Ordenamos por fecha y hora para que salgan en orden cronológico
        $consulta = "SELECT * FROM citas WHERE fecha >= '${fechaActual}' ORDER BY fecha ASC, hora ASC";

        try {
            // Ejecutamos la consulta usando el modelo Cita
            $citas = Cita::SQL($consulta);

            echo json_encode($citas);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public static function login() {
        // Permitimos GET para probar en navegador
        if($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
            
            // Si es GET (navegador), usamos datos de prueba falsos
            // Si es POST (app real), usamos lo que viene en el formulario
            $email = $_POST['email'] ?? 'correo@prueba.com';
            $password = $_POST['password'] ?? '123456';

            // Instanciamos el microservicio AuthService
            $auth = new AuthService();
            
            // Intentamos autenticar
            // NOTA: Asegúrate de tener un usuario real en tu BD con estos datos para que salga "true"
            $resultado = $auth->autenticar($email, $password);

            echo json_encode($resultado);
        }
    }
}
