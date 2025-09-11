<?php

namespace Model;
use Models\ActiveRecord;

class Valoracion extends ActiveRecord {
    protected static $tabla = 'valoraciones';
    protected static $columnasDB = ['id','usuario_id','servicio_id','estrellas','comentario','creado'];

    public $id;
    public $usuario_id;
    public $servicio_id;
    public $estrellas;
    public $comentario;
    public $creado;

    public function __construct($args = []) {
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->servicio_id = $args['servicio_id'] ?? null;
        $this->estrellas   = $args['estrellas']   ?? null;
        $this->comentario  = $args['comentario']  ?? '';
        $this->creado      = date('Y-m-d H:i:s');
    }
}
