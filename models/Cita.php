<?php

namespace Models;

class Cita extends ActiveRecord {

    // Base de datos:
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora', 'usuarioId', 'nombre_cliente'];

    public $id;
    public $fecha;
    public $hora;
    public $usuarioId;
    public $nombre_cliente;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->nombre_cliente = $args['nombre_cliente'] ?? '';
    }
}