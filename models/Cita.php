<?php

namespace Models;

class Cita extends ActiveRecord {

    // Base de datos:
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora', 'usuarioId'];

    public $id;
    public $fecha;
    public $hora;
    public $nombre_cliente;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora = $args['hora'] ?? '';
        $this->nombre_cliente = $args['nombre_cliente'] ?? '';
    }
}