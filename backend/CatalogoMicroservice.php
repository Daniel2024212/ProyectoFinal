<?php
namespace Backend; // <--- Namespace nuevo

use Models\Servicio;

class CatalogoMicroservice {
    public static function index() {
        if(!class_exists('Model\Servicio')) {
            if(file_exists(__DIR__ . '/../models/Servicio.php')) require_once __DIR__ . '/../models/Servicio.php';
        }
        
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }
}