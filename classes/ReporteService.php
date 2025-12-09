<?php
namespace Classes;

class ReporteService {
    public function generarResumenDiario() {
        // En un caso real, aquí harías un SUM(precio) en la BD
        // Simulamos datos para la API
        return [
            'fecha' => date('Y-m-d'),
            'citas_completadas' => 12,
            'ingresos_totales' => 1500.50,
            'empleado_del_dia' => 'Juan'
        ];
    }
}