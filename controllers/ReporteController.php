<?php

namespace Controllers;

use MVC\Router;
use Models\ActiveRecord; 

class ReporteController {

    public static function index(Router $router) {
        // Verificar autenticación (opcional, ajusta según tu proyecto)
        if(!isset($_SESSION)) session_start();
        
        // isAuth(); // Descomenta si tienes una función para proteger rutas

        $router->render('admin/reportes', [
            'titulo' => 'Reporte de Ventas'
        ]);
    }

    public static function api() {
        
        // 1. Obtener la instancia de la base de datos
        $db = ActiveRecord::getDB(); 

        // 2. Obtener fechas del GET o usar el mes actual por defecto
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        // 3. CONSULTA SQL (Cruza Citas con Servicios para sumar precios)
        $sql = "SELECT 
                    DATE(citas.fecha) as dia,
                    SUM(servicios.precio) as ingreso,
                    COUNT(DISTINCT citas.id) as cantidad
                FROM citas
                LEFT JOIN citasServicios ON citasServicios.citaId = citas.id
                LEFT JOIN servicios ON servicios.id = citasServicios.servicioId
                WHERE citas.fecha BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
                GROUP BY DATE(citas.fecha)
                ORDER BY DATE(citas.fecha) ASC";

        $resultado = $db->query($sql);
        
        $data = [];
        if($resultado) {
            while($row = $resultado->fetch_assoc()) {
                $data[] = [
                    'dia' => $row['dia'],
                    'ingreso' => $row['ingreso'] ?? 0, // Si es null pone 0
                    'cantidad' => $row['cantidad']
                ];
            }
        }

        echo json_encode($data);
    }
}