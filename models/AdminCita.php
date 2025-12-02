<?php

namespace Models;

class AdminCita extends ActiveRecord {
    protected static $tabla = 'citasServicios';
    // AGREGAMOS 'fecha' AQUÍ
    protected static $columnasDB = ['id', 'fecha', 'hora', 'cliente', 'email', 'telefono', 'servicio', 'precio'];

    // Y AQUÍ TAMBIÉN
    public $id;
    public $fecha; 
    public $hora;
    public $cliente;
    public $email;
    public $telefono;
    public $servicio;
    public $precio;

    public function __construct($args =[]) {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? ''; // Inicializar
        $this->hora = $args['hora'] ?? '';
        $this->cliente = $args['cliente'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->servicio = $args['servicio'] ?? '';
        $this->precio = $args['precio'] ?? '';
    }
}