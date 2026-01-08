<?php

namespace Models;

use Models\ActiveRecord;

class Usuario extends ActiveRecord {
    // Base de datos:
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'password', 'telefono', 'admin', 'confirmado'];

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $telefono;
    public $admin;
    public $confirmado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->admin = $args['admin'] ?? '0';
        $this->confirmado = $args['confirmado'] ?? '0';
    }

    public function validar_email(): array {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        return self::$alertas;
    }

    public function validar_password(): array {
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        
        if($this->password) {
            if(strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }
        }

        return self::$alertas;
    }

    public function validar_login(): array {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        
        return self::$alertas;
    }

    // Mensajes de validación para la creación de una cuenta:
    public function validar_nueva_cuenta(): array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        
        if(!$this->telefono) {
            self::$alertas['error'][] = 'El teléfono es obligatorio';
        }
        
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }

        if($this->password) {
            if(strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }
        }

        return self::$alertas;
    }

    // Revisa si el usuario ya existe:
    public function exite_usuario() {
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '" . $this->email . "' LIMIT 1;";

        $resultado = self::$db->query($query);

        if($resultado->num_rows) {
            self::$alertas['error'][] = 'El usuario ya esta registrado';
        }

        return $resultado;
    }

    // Hashear password:
    public function hash_password() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }  

    public function usuario_comprobado() {
        if($this->confirmado === '0') {
            self::$alertas['error'][] = 'Tu cuenta no ha sido confirmada';
        } else {
            return true;
        }
    }

    public function comprobar_password_and_verificado($password) {
        $resultado = password_verify($password, $this->password);

        if(!$resultado or !$this->confirmado) {
            self::$alertas['error'][] = 'Password incorrecto o tu cuenta no ha sido confirmada';
        } else {
            return true;
        }
    }
}