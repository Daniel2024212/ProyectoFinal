<?php 
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
// --- IMPORTAMOS LOS NUEVOS MICROSERVICIOS ---
use Controllers\CitaMicroservice;
use Controllers\CatalogoMicroservice;
use Controllers\AuthMicroservice;

// (Tus otros controladores normales...)
use Controllers\LoginController;
use Controllers\CitaController;
use Controllers\AdminController;
use Controllers\ServicioController; 

$router = new Router();

// ==========================================
// RUTA 1: VISTAS (Lo que ve el humano)
// ==========================================
$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);

// ... (Aquí van tus rutas de crear-cuenta, olvide, recuperar, etc.)

$router->get('/cita', [CitaController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);

// CRUD de Servicios (Admin)
$router->get('/servicios', [ServicioController::class, 'index']);
$router->get('/servicios/crear', [ServicioController::class, 'crear']);
$router->post('/servicios/crear', [ServicioController::class, 'crear']);
$router->get('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/eliminar', [ServicioController::class, 'eliminar']);


// ==========================================
// RUTA 2: MICROSERVICIOS API (Lo que usa JS)
// ==========================================

// Microservicio de Citas (Independiente)
$router->get('/api/citas', [CitaMicroservice::class, 'index']);
$router->post('/api/citas', [CitaMicroservice::class, 'guardar']);

// Microservicio de Catálogo (Independiente)
$router->get('/api/servicios', [CatalogoMicroservice::class, 'index']);

// Microservicio de Auth (Opcional)
$router->post('/api/auth/login', [AuthMicroservice::class, 'login']);


// Ejecutar
$router->comprobarRutas();