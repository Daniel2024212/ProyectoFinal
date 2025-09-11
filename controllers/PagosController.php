<?php

namespace Controllers;

use MVC\Router;
use Model\Pago;
use Database; // asegúrate de que Database.php tenga `class Database`
require_once __DIR__ . '/../config/Database.php';


class PagosController {

    public static function crear(Router $router) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pago = new Pago($_POST);
            $pago->guardar();

            // Conexión directa con la clase Database
            $db = (new \Database())->getConnection();
            $stmt = $db->prepare("UPDATE citas SET estado = 'confirmada' WHERE id = ?");
            $stmt->execute([$_POST['cita_id']]);

            echo json_encode(['success'=>true,'pago_id'=>$pago->id]);
        }
    }

    public static function listar(Router $router) {
        $cita_id = $_GET['cita_id'] ?? null;
        $pagos = Pago::where('cita_id', $cita_id);
        echo json_encode($pagos);
    }
}
