<?php
namespace Model;

class Valoracion extends ActiveRecord {
    protected static $tabla = 'valoraciones';
    protected static $columnasDB = ['id','usuario_id','cita_id','estrellas','comentario','created_at'];

    public $id;
    public $usuario_id;
    public $cita_id;
    public $estrellas;
    public $comentario;
    public $created_at;

    public function __construct($args = []) {
        $this->id         = $args['id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->cita_id    = $args['cita_id'] ?? null;
        $this->estrellas  = $args['estrellas'] ?? 0;
        $this->comentario = $args['comentario'] ?? '';
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
    }
}
