<?php
namespace backend;

use Models\Servicio;

class CatalogoMicroservice {
    public static function index() {
        // Consulta directa al modelo Servicio
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }
}