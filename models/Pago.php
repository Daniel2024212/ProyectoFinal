<?php
namespace Model;

class Pago extends ActiveRecord {
    protected static $tabla = 'pagos';
    protected static $columnasDB = ['id','usuario_id','cita_id','metodo','monto','created_at'];

    public $id;
    public $usuario_id;
    public $cita_id;
    public $metodo;
    public $monto;
    public $created_at;

    public function __construct($args = []) {
        $this->id         = $args['id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->cita_id    = $args['cita_id'] ?? null;
        $this->metodo     = $args['metodo'] ?? '';
        $this->monto      = $args['monto'] ?? 0;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
    }
}
