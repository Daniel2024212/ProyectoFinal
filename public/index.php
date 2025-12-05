<?php 

require_once __DIR__ . '/../includes/app.php';

use Controllers\AdminController;
use Controllers\APIController;
use Controllers\CitaController;
use Controllers\LoginController;
use Controllers\ServicioController;
use Controllers\ValoracionesController;
use Controllers\PagosController;
use MVC\Router;

$router = new Router();

// Iniciar sesión:
$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);

// Recuperar password:
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);
$router->get('/recuperar', [LoginController::class, 'recuperar']);
$router->post('/recuperar', [LoginController::class, 'recuperar']);

// Crear cuenta:
$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);

// Confirmar cuenta:
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);

// ÁREA PRIVADA:
$router->get('/cita', [CitaController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);

// API de Citas:
$router->get('/api/servicios', [APIController::class, 'index']);
// Consultar Citas Programadas (Filtro por Fecha)
$router->get('/api/citas/programadas', [APIController::class, 'programadas']);
// AHORA (Para probar en el navegador):
$router->get('/api/auth/login', [APIController::class, 'login']);
// --- NUEVA RUTA MICROSERVICIO AUTH (Con GET para probar) ---
$router->get('/api/auth/login', [APIController::class, 'login']);
// También agregamos POST para cuando uses la App real
$router->post('/api/auth/login', [APIController::class, 'login']);
$router->get('/api/notificar', [APIController::class, 'notificar']);

// POST: Para usarlo desde tu App o Postman (más seguro)
$router->post('/api/notificar', [APIController::class, 'notificar']);

// CRUD de servicios:
$router->get('/servicios', [ServicioController::class, 'index']);
$router->get('/servicios/crear', [ServicioController::class, 'crear']);
$router->post('/servicios/crear', [ServicioController::class, 'crear']);
$router->get('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/actualizar', [ServicioController::class, 'actualizar']);
$router->post('/servicios/eliminar', [ServicioController::class, 'eliminar']);


// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();

