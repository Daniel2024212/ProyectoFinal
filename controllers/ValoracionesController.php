<?php
namespace Controllers\API;
use MVC\Router;
use Model\Valoracion;
use Classes\ValoracionService;
use Models\Cita;      // Nota: Namespace plural

class ValoracionesController {

    public static function crear() {
        $input = json_decode(file_get_contents('php://input'), true);

        $usuarioId  = $input['usuario_id'] ?? null;
        $citaId     = $input['cita_id'] ?? null;
        $estrellas  = $input['estrellas'] ?? 0;
        $comentario = $input['comentario'] ?? '';

        // 1. Validaciones
        if(!$usuarioId || !$citaId) {
            echo json_encode(['success' => false, 'error' => 'Usuario y Cita son obligatorios']);
            return;
        }

        if($estrellas < 1 || $estrellas > 5) {
            echo json_encode(['success' => false, 'error' => 'La calificación debe ser de 1 a 5']);
            return;
        }

        // 2. Verificar existencia de la cita
        $cita = Cita::find($citaId);
        if(!$cita) {
            echo json_encode(['success' => false, 'error' => 'La cita no existe']);
            return;
        }

        // 3. Evitar duplicados (Regla de Negocio)
        // Usamos SQL directo ya que ActiveRecord básico a veces no tiene 'where' con múltiples condiciones
        $query = "SELECT * FROM valoraciones WHERE cita_id = '{$citaId}' AND usuario_id = '{$usuarioId}' LIMIT 1";
        $existe = Valoracion::SQL($query);

        if(!empty($existe)) {
            echo json_encode(['success' => false, 'error' => 'Ya valoraste esta cita anteriormente']);
            return;
        }

        // 4. Guardar
        $valoracion = new Valoracion([
            'usuario_id' => $usuarioId,
            'cita_id'    => $citaId,
            'estrellas'  => $estrellas,
            'comentario' => htmlspecialchars($comentario) // Sanitizar HTML básico
        ]);

        $resultado = $valoracion->guardar();

        echo json_encode([
            'success' => $resultado['resultado'],
            'mensaje' => $resultado['resultado'] ? 'Valoración guardada' : 'Error al guardar'
        ]);
    }
}