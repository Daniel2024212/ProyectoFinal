<?php

namespace Controllers;

use MVC\Router;
use Classes\CitaService; 

class AdminController {

    public static function index(Router $router) {
        session_start();

        // Verificaciones de acceso (Middleware)
        if(!isset($_SESSION['admin'])) {
            header('Location: /');
        }

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $fechas = explode('-', $fecha);

        if(!checkdate($fechas[1], $fechas[2], $fechas[0])) {
            header('Location: /404');
        }

        // CORRECCIÓN: El método se llama obtenerAgendaPorFecha en el servicio
        $citas = CitaService::obtenerAgendaPorFecha($fecha);
        
        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'],
            'citas' => $citas,
            'fecha' => $fecha
        ]);
    }
}