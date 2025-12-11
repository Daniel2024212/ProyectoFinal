<?php

namespace Models;

class Cita extends ActiveRecord {
    // 1. Agregamos 'cliente' a las columnas de la BD
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora', 'usuarioId', 'cliente'];

    public $id;
    public $fecha;
    public $hora;
    public $usuarioId;
    public $cliente; // 2. Agregamos la propiedad pública

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->cliente = $args['cliente'] ?? ''; // 3. La inicializamos
    }
}