<?php

namespace Model;

use Models\ActiveRecord;

class Valoracion extends ActiveRecord {
    protected static $tabla = 'valoraciones';
    // CAMBIO: Usamos 'cita_id' en lugar de 'servicio_id' para vincular la reseña a un evento real
    protected static $columnasDB = ['id','usuario_id','cita_id','estrellas','comentario','creado'];

    public $id;
    public $usuario_id;
    public $cita_id; 
    public $estrellas;
    public $comentario;
    public $creado;

    public function __construct($args = []) {
        $this->id          = $args['id'] ?? null;
        $this->usuario_id  = $args['usuario_id'] ?? null;
        $this->cita_id     = $args['cita_id'] ?? null; // Enlace a la tabla citas
        $this->estrellas   = $args['estrellas']   ?? null;
        $this->comentario  = $args['comentario']  ?? '';
        $this->creado      = date('Y-m-d H:i:s');
    }
}