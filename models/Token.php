<?php

namespace Models;

use Models\ActiveRecord;

class Token extends ActiveRecord {
    // Base de datos:
    protected static $tabla = 'tokens';
    protected static $columnasDB = ['id', 'usuarioId', 'token'];

    public $id;
    public $usuarioId;
    public $token;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuarioId = $args['usuarioId'] ?? '';
        $this->token = $args['token'] ?? '';
    }

    public function crear_token() {
        $this->token = uniqid();
    }

}