<?php

use Dotenv\Dotenv;
use Models\ActiveRecord;

require __DIR__ . '/../vendor/autoload.php';

// --- SOLUCIÓN ERROR 500: CARGA MANUAL DE SERVICIOS ---
// Agregamos esto porque el autoloader del servidor no se ha actualizado
require_once __DIR__ . '/../classes/Email.php';
require_once __DIR__ . '/../classes/AuthService.php';
require_once __DIR__ . '/../classes/CatalogoService.php';
require_once __DIR__ . '/../classes/CitaService.php';
require_once __DIR__ . '/../classes/PagoService.php';
require_once __DIR__ . '/../classes/ValoracionService.php';
// -----------------------------------------------------

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
ActiveRecord::setDB($db);