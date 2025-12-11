<?php 
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
// Importamos los 3 Microservicios
use Controllers\AuthMicroservice;
use Controllers\CitaMicroservice;
use Controllers\CatalogoMicroservice;
use Controllers\LoginController; // Para las vistas HTML

$router = new Router();

// --- ZONA VISTAS (Frontend) ---
$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);
$router->get('/recuperar', [LoginController::class, 'recuperar']);
$router->post('/recuperar', [LoginController::class, 'recuperar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/cita', [Controllers\CitaController::class, 'index']); // Vista de la app
$router->get('/admin', [Controllers\AdminController::class, 'index']); 


// --- ZONA MICROSERVICIOS (Backend API) ---

// 1. Microservicio de Catálogo
$router->get('/api/servicios', [CatalogoMicroservice::class, 'index']);

// 2. Microservicio de Citas
$router->get('/api/citas', [CitaMicroservice::class, 'index']);  // Ver citas
$router->post('/api/citas', [CitaMicroservice::class, 'guardar']); // Guardar cita

// 3. Microservicio de Autenticación
$router->get('/api/auth/login', [AuthMicroservice::class, 'login']);


// --- EJECUCIÓN ---
$router->comprobarRutas();