<?php

namespace Model;

class Valoracion extends ActiveRecord {
    // CAMBIO AQUÍ: Apuntamos a la nueva tabla
    protected static $tabla = 'resenas'; 
    
    // El resto de columnas se mantiene igual
    protected static $columnasDB = ['id', 'usuarioId', 'citaId', 'calificacion', 'comentario', 'fecha'];

    public $id;
    public $usuarioId;
    public $citaId;
    public $calificacion;
    public $comentario;
    public $fecha;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->citaId = $args['citaId'] ?? '';
        $this->calificacion = $args['calificacion'] ?? '';
        $this->comentario = $args['comentario'] ?? '';
        $this->fecha = date('Y-m-d H:i:s');
    }

    public function validar() {
        if(!$this->calificacion) {
            self::$alertas['error'][] = 'Debes seleccionar una estrella';
        }
        if(!$this->comentario) {
            self::$alertas['error'][] = 'El comentario es obligatorio';
        }
        return self::$alertas;
    }
}