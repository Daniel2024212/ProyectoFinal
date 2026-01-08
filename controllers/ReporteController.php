<?php

namespace Controllers;

use MVC\Router; // Asegúrate que este namespace coincida con tu Router
// use Model\Cita; // Si usas un modelo de Cita, impórtalo aquí

class ReporteController {

    public static function index(Router $router) {
        // Aquí puedes hacer validaciones de admin (session_start, isAuth, etc.)
        
        $router->render('admin/reportes', [
            'titulo' => 'Reporte de Ingresos'
        ]);
    }

    public static function api() {
        // Esta es la parte que antes era "get_reporte_data.php"
        
        // Incluir conexión a BD (Depende de cómo manejes tu BD en el proyecto, 
        // usualmente en MVC ya tienes una clase Active Record o Database)
        // Ejemplo genérico usando tu DB global si existe:
        global $db; 

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        $query = "SELECT DATE(fecha) as dia, SUM(total) as ingreso, COUNT(*) as cantidad 
                  FROM citas 
                  WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                  GROUP BY DATE(fecha)";

        // Ejecutar query (Ajusta esto según tu clase de Base de Datos)
        $citas = $db->query($query); 
        
        $data = [];
        while($row = $citas->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);
    }
}