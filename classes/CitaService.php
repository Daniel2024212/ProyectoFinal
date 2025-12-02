<?php
namespace Classes;

use Models\Cita;
use Models\CitaServicio;
use Models\AdminCita; // Modelo para consultas complejas

class CitaService {
    
    // Crear una nueva reserva
    public static function agendarCita(array $datos): array {
        // 1. Guardar la Cabecera de la Cita
        $cita = new Cita($datos);
        $resultado = $cita->guardar();

        if(!$resultado['resultado']) {
            return ['success' => false, 'error' => 'Error al crear la cita'];
        }

        $citaId = $resultado['id'];

        // 2. Guardar los detalles (Servicios solicitados)
        // Recibe string "1,2,4" y lo convierte en array
        $serviciosId = explode(",", $datos['servicios']); 
        
        foreach($serviciosId as $idServicio) {
            $citaServicio = new CitaServicio([
                'citaId' => $citaId,
                'servicioId' => $idServicio
            ]);
            $citaServicio->guardar();
        }

        return ['success' => true, 'cita_id' => $citaId];
    }

    // Consultar agenda por fecha (Para el Admin)
    public static function obtenerAgendaPorFecha($fecha) {
        // Consulta SQL optimizada que une tablas
        $consulta = "SELECT citas.id, citas.hora, CONCAT( usuarios.nombre, ' ', usuarios.apellido) as cliente, ";
        $consulta .= " usuarios.email, usuarios.telefono, servicios.nombre as servicio, servicios.precio  ";
        $consulta .= " FROM citas  ";
        $consulta .= " LEFT OUTER JOIN usuarios ON citas.usuarioId=usuarios.id  ";
        $consulta .= " LEFT OUTER JOIN citasServicios ON citasServicios.citaId=citas.id ";
        $consulta .= " LEFT OUTER JOIN servicios ON servicios.id=citasServicios.servicioId ";
        $consulta .= " WHERE fecha =  '{$fecha}' ";

        return AdminCita::SQL($consulta);
    }
}