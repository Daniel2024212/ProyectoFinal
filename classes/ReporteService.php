<?php

namespace Classes;

// 1. Carga manual de Modelos (Defensa contra errores de Linux)
if(file_exists(__DIR__ . '/../models/Cita.php')) {
    require_once __DIR__ . '/../models/Cita.php';
}

use models\Cita;

class ReporteService {

    public function generarResumenDiario() {
        
        // Verificamos que el modelo exista para no romper la API
        if(!class_exists('Model\Cita')) {
            return ['error' => 'No se pudo cargar el Modelo Cita'];
        }

        $fechaHoy = date('Y-m-d');

        // --- CONSULTA 1: Total de Citas de Hoy ---
        // Contamos cuántas filas hay en la tabla citas con la fecha de hoy
        $queryCitas = "SELECT COUNT(*) as total FROM citas WHERE fecha = '{$fechaHoy}'";
        
        try {
            $resultadoCitas = Cita::SQL($queryCitas);
            // Active Record devuelve un array de objetos, tomamos el valor 'total'
            $totalCitas = $resultadoCitas ? array_shift($resultadoCitas)->total : 0;
        } catch (\Exception $e) {
            $totalCitas = 0;
        }

        // --- CONSULTA 2: Ingresos Totales (Suma de Precios) ---
        // Hacemos un JOIN para sumar el precio de los servicios asociados a las citas de hoy.
        // NOTA: Revisa si tu tabla pivote se llama 'citasservicios' o 'citasServicios' en tu BD.
        $queryIngresos = "
            SELECT SUM(servicios.precio) as total
            FROM citas
            LEFT JOIN citasservicios ON citas.id = citasservicios.citaId
            LEFT JOIN servicios ON servicios.id = citasservicios.servicioId
            WHERE citas.fecha = '{$fechaHoy}'
        ";

        try {
            $resultadoIngresos = Cita::SQL($queryIngresos);
            $totalIngresos = $resultadoIngresos ? array_shift($resultadoIngresos)->total : 0;
        } catch (\Exception $e) {
            $totalIngresos = 0;
        }

        // Devolvemos los datos REALES
        return [
            'fecha_consulta' => $fechaHoy,
            'citas_hoy' => $totalCitas,
            'ingresos_reales' => $totalIngresos ?? 0, // Si es null, pon 0
            'moneda' => 'MXN',
            'estado' => 'Datos en Tiempo Real'
        ];
    }
}