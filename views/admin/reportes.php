<?php
// --- 1. CONFIGURACIÓN Y CONEXIÓN ---
ini_set('display_errors', 0);
error_reporting(E_ALL);

global $db;
// Intentar recuperar la conexión si no existe
if (empty($db)) {
    $ruta_db = __DIR__ . '/../../includes/database.php';
    if (file_exists($ruta_db)) include_once $ruta_db;
}
// Validación final
if (empty($db)) {
    die("<div style='text-align:center; padding:20px; color:red;'>Error: No se pudo conectar a la base de datos.</div>");
}

// --- 2. LÓGICA DE FILTRADO ---
$fecha_seleccionada = $_GET['fecha'] ?? null;
$modo_dia = !empty($fecha_seleccionada);

// Variables por defecto
$titulo = "Reporte General";
$total_ingresos = 0;
$total_citas = 0;
$top_servicio = "N/A";
$lista_citas_dia = null;

// --- CASO A: REPORTE POR DÍA ---
if ($modo_dia) {
    $titulo = "Reporte del día: " . date("d/m/Y", strtotime($fecha_seleccionada));
    
    // 1. Totales del día
    $sql_dia = "SELECT COUNT(DISTINCT c.id) as cant, SUM(s.precio) as total 
                FROM citas c 
                LEFT JOIN citasServicios cs ON c.id = cs.citaId 
                LEFT JOIN servicios s ON cs.servicioId = s.id 
                WHERE c.fecha = '$fecha_seleccionada'";
    $res_dia = mysqli_query($db, $sql_dia);
    $data_dia = mysqli_fetch_assoc($res_dia);
    $total_ingresos = $data_dia['total'] ?? 0;
    $total_citas = $data_dia['cant'] ?? 0;

    // 2. Tabla detallada
    $sql_lista = "SELECT TIME_FORMAT(c.hora, '%H:%i') as hora, CONCAT(u.nombre, ' ', u.apellido) as cliente, 
                  GROUP_CONCAT(s.nombre SEPARATOR ', ') as servicios, SUM(s.precio) as total_cita 
                  FROM citas c 
                  JOIN usuarios u ON c.usuarioId = u.id 
                  LEFT JOIN citasServicios cs ON c.id = cs.citaId 
                  LEFT JOIN servicios s ON cs.servicioId = s.id 
                  WHERE c.fecha = '$fecha_seleccionada' 
                  GROUP BY c.id ORDER BY c.hora ASC";
    $lista_citas_dia = mysqli_query($db, $sql_lista);
} 
// --- CASO B: REPORTE GENERAL ---
else {
    // 1. Totales Históricos
    $sql_hist = "SELECT SUM(s.precio) as total FROM citas c JOIN citasServicios cs ON c.id = cs.citaId JOIN servicios s ON cs.servicioId = s.id";
    $res_hist = mysqli_query($db, $sql_hist);
    $total_ingresos = mysqli_fetch_assoc($res_hist)['total'] ?? 0;

    $sql_count = "SELECT COUNT(DISTINCT id) as cant FROM citas";
    $res_count = mysqli_query($db, $sql_count);
    $total_citas = mysqli_fetch_assoc($res_count)['cant'] ?? 0;

    // 2. Gráfica Línea
    $sql_g1 = "SELECT c.fecha, SUM(s.precio) as total FROM citas c JOIN citasServicios cs ON c.id = cs.citaId JOIN servicios s ON cs.servicioId = s.id GROUP BY c.fecha ORDER BY c.fecha ASC";
    $res_g1 = mysqli_query($db, $sql_g1);
    $fechas = []; $ingresos_data = [];
    while($r = mysqli_fetch_assoc($res_g1)) {
        $fechas[] = date('d-M', strtotime($r['fecha']));
        $ingresos_data[] = $r['total'];
    }

    // 3. Gráfica Dona
    $sql_g2 = "SELECT s.nombre, COUNT(cs.id) as cant FROM servicios s JOIN citasServicios cs ON s.id = cs.servicioId GROUP BY s.nombre ORDER BY cant DESC LIMIT 5";
    $res_g2 = mysqli_query($db, $sql_g2);
    $serv_nombres = []; $serv_cant = [];
    while($r = mysqli_fetch_assoc($res_g2)) {
        $serv_nombres[] = $r['nombre'];
        $serv_cant[] = $r['cant'];
    }
    $top_servicio = $serv_nombres[0] ?? "N/A";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- ESTILOS LIGHT MODE (Limpio y sin cosas negras extrañas) --- */
        :root {
            --bg-body: #f4f6f9;       /* Fondo gris muy suave */
            --bg-panel: #ffffff;      /* Fondo blanco para tarjetas */
            --text-main: #333333;     /* Texto oscuro */
            --text-light: #777777;    /* Texto secundario */
            --primary: #007bff;       /* Azul principal */
            --accent: #28a745;        /* Verde para dinero */
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden; /* Evita scroll doble */
            display: flex;
            flex-direction: column;
        }

        /* Layout dividido */
        .contenedor-reporte {
            display: flex;
            flex: 1;
            height: 100%;
            overflow: hidden;
        }

        /* IZQUIERDA: Imagen */
        .panel-imagen {
            width: 35%;
            background-image: url('../../build/img/barber-bg.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        /* Sombra sobre la imagen para estilo */
        .panel-imagen::after {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.3);
        }

        /* DERECHA: Datos (Blanco) */
        .panel-datos {
            width: 65%;
            padding: 40px;
            overflow-y: auto; /* Scroll vertical solo si es necesario */
            overflow-x: hidden; /* IMPORTANTE: Quita la barra horizontal negra */
            background-color: var(--bg-body);
        }

        /* Títulos */
        h2 { font-weight: 700; margin-bottom: 25px; color: #2c3e50; font-size: 28px; }

        /* Filtro */
        .filtro-box {
            background: var(--bg-panel);
            padding: 15px 25px;
            border-radius: 50px; /* Redondeado moderno */
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        .filtro-box label { font-size: 14px; font-weight: 600; color: var(--text-light); }
        .filtro-box input {
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; color: #555; outline: none;
        }
        .btn {
            padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 600;
            transition: 0.3s; border: none; cursor: pointer; display: inline-block;
        }
        .btn-blue { background: var(--primary); color: white; }
        .btn-blue:hover { background: #0056b3; box-shadow: 0 4px 10px rgba(0,123,255,0.3); }
        .btn-red { background: #dc3545; color: white; margin-left: 10px; }
        .btn-red:hover { background: #a71d2a; }

        /* Tarjetas KPIs */
        .grid-kpis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        .card {
            background: var(--bg-panel);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); /* Sombra suave */
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-5px); }
        .card h3 { font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .card .valor { font-size: 32px; font-weight: 700; color: #333; }
        
        /* Gráficas y Tablas */
        .contenedor-graficas {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            height: 350px;
        }
        .box-white {
            background: var(--bg-panel);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: 100%;
            overflow: hidden;
        }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; color: var(--text-light); font-size: 13px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        td { padding: 15px 0; border-bottom: 1px solid #eee; font-size: 15px; color: #444; }
        .precio-row { font-weight: 700; color: var(--accent); }

        /* Responsive */
        @media (max-width: 900px) {
            .contenedor-reporte { flex-direction: column; overflow: auto; }
            .panel-imagen { width: 100%; height: 200px; }
            .panel-datos { width: 100%; height: auto; }
            .grid-kpis, .contenedor-graficas { grid-template-columns: 1fr; }
            .contenedor-graficas { height: auto; }
            .box-white { height: 300px; margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<div class="contenedor-reporte">
    <div class="panel-imagen"></div>

    <div class="panel-datos">
        
        <div class="filtro-box">
            <form method="GET" style="display:flex; align-items:center; gap:10px;">
                <label>📅 Filtrar por fecha:</label>
                <input type="date" name="fecha" value="<?php echo $fecha_seleccionada; ?>">
                <button type="submit" class="btn btn-blue">Buscar</button>
            </form>
            <?php if($modo_dia): ?>
                <a href="reportes.php" class="btn btn-red">Ver Todo</a>
            <?php endif; ?>
        </div>

        <h2><?php echo $titulo; ?></h2>

        <div class="grid-kpis">
            <div class="card">
                <h3>Ingresos Totales</h3>
                <div class="valor" style="color: var(--accent);">$ <?php echo number_format($total_ingresos, 0); ?></div>
            </div>
            <div class="card">
                <h3>Citas Realizadas</h3>
                <div class="valor" style="color: var(--primary);"><?php echo $total_citas; ?></div>
            </div>
            <div class="card">
                <h3>Servicio Top</h3>
                <div class="valor" style="font-size: 20px;"><?php echo $modo_dia ? '(Ver tabla)' : $top_servicio; ?></div>
            </div>
        </div>

        <?php if($modo_dia): ?>
            
            <div class="box-white" style="height: auto;">
                <h3 style="margin-bottom:20px; color:#555;">Detalle de Citas</h3>
                <?php if(mysqli_num_rows($lista_citas_dia) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Servicios</th>
                                <th>Cobro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cita = mysqli_fetch_assoc($lista_citas_dia)): ?>
                                <tr>
                                    <td><strong><?php echo $cita['hora']; ?></strong></td>
                                    <td><?php echo $cita['cliente']; ?></td>
                                    <td style="color:#777; font-size:13px;"><?php echo $cita['servicios']; ?></td>
                                    <td class="precio-row">$ <?php echo number_format($cita['total_cita'], 0); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center; padding:30px; color:#999;">No hay citas registradas para este día.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="contenedor-graficas">
                <div class="box-white">
                    <canvas id="chartLine"></canvas>
                </div>
                <div class="box-white">
                    <canvas id="chartDona"></canvas>
                </div>
            </div>

            <script>
                // Colores para fondo blanco
                Chart.defaults.color = '#666';
                Chart.defaults.borderColor = '#eee';
                Chart.defaults.font.family = 'Poppins';

                new Chart(document.getElementById('chartLine'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($fechas); ?>,
                        datasets: [{
                            label: 'Ingresos',
                            data: <?php echo json_encode($ingresos_data); ?>,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            fill: true, tension: 0.3, pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: {display:false} } }
                });

                new Chart(document.getElementById('chartDona'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($serv_nombres); ?>,
                        datasets: [{
                            data: <?php echo json_encode($serv_cant); ?>,
                            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: {position:'right'} } }
                });
            </script>

        <?php endif; ?>

    </div>
</div>

</body>
</html>