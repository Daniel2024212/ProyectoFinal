<?php
// --- 1. CONFIGURACIÓN Y CONEXIÓN ---
ini_set('display_errors', 0);
error_reporting(E_ALL);

global $db;
if (empty($db)) {
    $ruta_db = __DIR__ . '/../../includes/database.php';
    if (file_exists($ruta_db)) include_once $ruta_db;
}
if (empty($db)) die("Error de conexión.");

// --- 2. LÓGICA DE FILTRADO ---
$fecha_seleccionada = $_GET['fecha'] ?? null; // ¿El usuario eligió una fecha?
$modo_dia = !empty($fecha_seleccionada); // true si estamos viendo un día, false si es general

// VARIABLES INICIALES
$titulo = "Reporte Histórico General";
$total_ingresos = 0;
$total_citas = 0;
$top_servicio = "N/A";
$lista_citas_dia = null; // Solo se llenará si hay fecha seleccionada

// --- CASO A: REPORTE POR DÍA ESPECÍFICO ---
if ($modo_dia) {
    $titulo = "Reporte del día: " . date("d-m-Y", strtotime($fecha_seleccionada));
    
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

    // 2. Lista detallada (Tabla)
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

// --- CASO B: REPORTE GENERAL (HISTÓRICO) ---
else {
    // 1. Totales Históricos
    $sql_hist = "SELECT SUM(s.precio) as total FROM citas c JOIN citasServicios cs ON c.id = cs.citaId JOIN servicios s ON cs.servicioId = s.id";
    $res_hist = mysqli_query($db, $sql_hist);
    $total_ingresos = mysqli_fetch_assoc($res_hist)['total'] ?? 0;

    $sql_count = "SELECT COUNT(DISTINCT id) as cant FROM citas";
    $res_count = mysqli_query($db, $sql_count);
    $total_citas = mysqli_fetch_assoc($res_count)['cant'] ?? 0;

    // 2. Datos para Gráfica de Línea
    $sql_g1 = "SELECT c.fecha, SUM(s.precio) as total FROM citas c JOIN citasServicios cs ON c.id = cs.citaId JOIN servicios s ON cs.servicioId = s.id GROUP BY c.fecha ORDER BY c.fecha ASC";
    $res_g1 = mysqli_query($db, $sql_g1);
    $fechas = []; $ingresos_data = [];
    while($r = mysqli_fetch_assoc($res_g1)) {
        $fechas[] = date('d-M', strtotime($r['fecha']));
        $ingresos_data[] = $r['total'];
    }

    // 3. Datos para Gráfica Top Servicios
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
    <title>Panel de Reportes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-main: #121212;
            --card-bg: #1e1e1e;
            --text-main: #fff;
            --accent: #3498db;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .contenedor-reporte { display: flex; flex: 1; overflow: hidden; }
        
        /* Panel Izquierdo */
        .panel-imagen {
            width: 35%;
            background-image: url('../../build/img/barber-bg.jpg');
            background-size: cover; background-position: center;
            position: relative;
        }
        .panel-imagen::after { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.6); }

        /* Panel Derecho */
        .panel-datos {
            width: 65%;
            padding: 30px;
            overflow-y: auto;
            background: transparent; /* Fondo transparente solicitado */
        }

        /* Formulario de Filtro */
        .filtro-container {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .filtro-container input[type="date"] {
            padding: 10px; border-radius: 8px; border: none; outline: none;
            background: #333; color: white; font-family: 'Poppins', sans-serif;
        }
        .btn {
            padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer;
            font-weight: 600; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 14px;
        }
        .btn-buscar { background: var(--accent); color: white; }
        .btn-buscar:hover { background: #2980b9; }
        .btn-reset { background: #e74c3c; color: white; }
        
        h2 { margin-bottom: 20px; font-weight: 800; }

        /* Tarjetas KPIs */
        .grid-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .card {
            background: var(--card-bg);
            padding: 20px; border-radius: 16px; text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .card span { font-size: 12px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
        .card .val { font-size: 28px; font-weight: 800; margin-top: 5px; color: #fff; }

        /* Tablas y Gráficas */
        .content-box {
            background: var(--card-bg);
            padding: 20px; border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        /* Estilos Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; color: #aaa; padding-bottom: 10px; border-bottom: 1px solid #333; font-size: 13px; }
        td { padding: 15px 0; border-bottom: 1px solid #333; font-size: 14px; }

        /* Estilos Gráficas */
        .grid-charts { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; height: 300px; }

        @media (max-width: 900px) {
            .contenedor-reporte { flex-direction: column; }
            .panel-imagen { width: 100%; height: 200px; }
            .panel-datos { width: 100%; }
            .grid-kpis, .grid-charts { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="contenedor-reporte">
    <div class="panel-imagen"></div>
    
    <div class="panel-datos">
        
        <div class="filtro-container">
            <form method="GET" style="display:flex; gap:10px; align-items:center; width:100%;">
                <label style="color:#aaa; font-size:14px;">Filtrar por día:</label>
                <input type="date" name="fecha" value="<?php echo $fecha_seleccionada; ?>">
                <button type="submit" class="btn btn-buscar">🔍 Buscar</button>
                
                <?php if($modo_dia): ?>
                    <a href="reportes.php" class="btn btn-reset">❌ Ver General</a>
                <?php endif; ?>
            </form>
        </div>

        <h2><?php echo $titulo; ?></h2>

        <div class="grid-kpis">
            <div class="card">
                <span>Ingresos Totales</span>
                <div class="val" style="color:#2ecc71">$ <?php echo number_format($total_ingresos, 0); ?></div>
            </div>
            <div class="card">
                <span>Citas Atendidas</span>
                <div class="val" style="color:#3498db"><?php echo $total_citas; ?></div>
            </div>
            <div class="card">
                <span>Top Servicio</span>
                <div class="val" style="font-size:18px;">
                    <?php echo $modo_dia ? 'Ver tabla abajo' : $top_servicio; ?>
                </div>
            </div>
        </div>

        <?php if ($modo_dia): ?>
            
            <div class="content-box">
                <h3 style="margin-bottom:15px;">Detalle de Citas</h3>
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
                                    <td style="font-weight:bold; color:#3498db"><?php echo $cita['hora']; ?></td>
                                    <td><?php echo $cita['cliente']; ?></td>
                                    <td style="color:#aaa; font-size:12px;"><?php echo $cita['servicios']; ?></td>
                                    <td style="font-weight:bold;">$ <?php echo number_format($cita['total_cita'], 0); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align:center; color:#aaa; padding:20px;">No hay citas registradas en esta fecha.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="grid-charts">
                <div class="content-box" style="margin:0;">
                    <canvas id="chartLine"></canvas>
                </div>
                <div class="content-box" style="margin:0;">
                    <canvas id="chartDoughnut"></canvas>
                </div>
            </div>

            <script>
                // Solo cargamos gráficas en modo general
                Chart.defaults.color = '#aaa';
                Chart.defaults.borderColor = '#333';
                
                new Chart(document.getElementById('chartLine'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($fechas); ?>,
                        datasets: [{
                            label: 'Ingresos Históricos',
                            data: <?php echo json_encode($ingresos_data); ?>,
                            borderColor: '#3498db', backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            fill: true, tension: 0.4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: {display:false} } }
                });

                new Chart(document.getElementById('chartDoughnut'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($serv_nombres); ?>,
                        datasets: [{
                            data: <?php echo json_encode($serv_cant); ?>,
                            backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#2ecc71', '#9b59b6'],
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