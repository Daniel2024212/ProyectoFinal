<?php

namespace Model;

use Models\ActiveRecord;

class Valoracion extends ActiveRecord {
    protected static $tabla = 'valoraciones';
    // Sincronizado con SQL: id, cita_id, usuario_id, estrellas, comentario, creado
    protected static $columnasDB = ['id', 'cita_id', 'usuario_id', 'estrellas', 'comentario', 'creado'];

    public $id;
    public $cita_id;
    public $usuario_id;
    public $estrellas;
    public $comentario;
    public $creado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->cita_id = $args['cita_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->estrellas = $args['estrellas'] ?? null;
        $this->comentario = $args['comentario'] ?? '';
        $this->creado = date('Y-m-d H:i:s');
    }
}