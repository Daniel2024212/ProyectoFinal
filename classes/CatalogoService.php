<?php
namespace Classes;

use Models\Servicio;

class CatalogoService {
    
    // Obtener listado (API pública y Admin)
    public static function listarServicios(): array {
        return Servicio::all();
    }

    // Obtener un servicio específico por ID
    public static function obtenerServicio(int $id) {
        return Servicio::find($id);
    }

    // Gestión administrativa (Crear/Actualizar)
    public static function guardarServicio(array $datos): array {
        $servicio = new Servicio($datos);
        $alertas = $servicio->validar();

        if(empty($alertas)) {
            $resultado = $servicio->guardar();
            return ['success' => true, 'id' => $resultado['id']];
        }
        return ['success' => false, 'alertas' => $alertas];
    }
}