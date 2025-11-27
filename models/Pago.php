<?php

namespace Model;

use Models\ActiveRecord;

class Pago extends ActiveRecord {
    protected static $tabla = 'pagos';
    // Aseguramos que 'estado' y 'referencia' estén aquí para que ActiveRecord los guarde
    protected static $columnasDB = ['id','cita_id','usuario_id','monto','metodo','estado','referencia','creado'];

    public $id;
    public $cita_id;
    public $usuario_id;
    public $monto;
    public $metodo;
    public $estado;
    public $referencia;
    public $creado;

    public function __construct($args = []) {
        $this->id        = $args['id'] ?? null;
        $this->cita_id   = $args['cita_id'] ?? null;
        $this->usuario_id= $args['usuario_id'] ?? null;
        $this->monto     = $args['monto'] ?? 0;
        $this->metodo    = $args['metodo'] ?? 'efectivo';
        $this->estado    = $args['estado'] ?? 'pendiente';
        $this->referencia= $args['referencia'] ?? '';
        $this->creado    = date('Y-m-d H:i:s');
    }
}