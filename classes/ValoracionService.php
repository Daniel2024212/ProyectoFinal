<?php
namespace Classes;

use Model\Valoracion;

class ValoracionService {

    public static function crearValoracion(array $datos): array {
        if(($datos['estrellas'] ?? 0) < 1) {
            return ['success' => false, 'error' => 'Calificación obligatoria'];
        }

        $valoracion = new Valoracion([
            'usuario_id' => $datos['usuario_id'],
            'cita_id'    => $datos['cita_id'],
            'estrellas'  => $datos['estrellas'],
            'comentario' => $datos['comentario'] ?? ''
        ]);

        $resultado = $valoracion->guardar();

        return ['success' => $resultado['resultado']];
    }
}