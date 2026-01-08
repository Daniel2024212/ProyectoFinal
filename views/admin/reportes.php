<?php
// --- 1. CONFIGURACIÓN Y CONEXIÓN (Robusta) ---
ini_set('display_errors', 0); // Apagamos errores en producción para que se vea limpio
error_reporting(E_ALL);

global $db;
if (empty($db)) {
    $ruta_db = __DIR__ . '/../../includes/database.php';
    if (file_exists($ruta_db)) {
        include_once $ruta_db;
    }
}
if (empty($db)) { die("Error Crítico: No hay conexión a la base de datos."); }

// --- 2. LÓGICA DE DATOS "POR DÍA" (LO NUEVO) ---
$hoy = date('Y-m-d');

// A) Totales de HOY (KPIs)
$sql_hoy_totales = "
    SELECT 
        COUNT(DISTINCT c.id) as citas_hoy, 
        SUM(s.precio) as ventas_hoy
    FROM citas c
    LEFT JOIN citasServicios cs ON c.id = cs.citaId
    LEFT JOIN servicios s ON cs.servicioId = s.id
    WHERE c.fecha = '$hoy'";

$resultado_hoy = mysqli_query($db, $sql_hoy_totales);
$datos_hoy = mysqli_fetch_assoc($resultado_hoy);

$ventas_hoy = $datos_hoy['ventas_hoy'] ?? 0;
$citas_hoy = $datos_hoy['citas_hoy'] ?? 0;

// B) Lista detallada de citas de HOY (Para la tabla)
$sql_lista_hoy = "
    SELECT 
        TIME_FORMAT(c.hora, '%H:%i') as hora_fmt,
        CONCAT(u.nombre, ' ', u.apellido) as cliente,
        GROUP_CONCAT(s.nombre SEPARATOR ', ') as servicios_lista,
        SUM(s.precio) as total_cita
    FROM citas c
    INNER JOIN usuarios u ON c.usuarioId = u.id
    LEFT JOIN citasServicios cs ON c.id = cs.citaId
    LEFT JOIN servicios s ON cs.servicioId = s.id
    WHERE c.fecha = '$hoy'
    GROUP BY c.id
    ORDER BY c.hora ASC";
$resultado_lista_hoy = mysqli_query($db, $sql_lista_hoy);


// --- 3. LÓGICA DE DATOS HISTÓRICOS (PARA GRÁFICAS) ---

// C) Histórico de Ingresos (Gráfica Línea)
$sql_historico = "
    SELECT c.fecha, SUM(s.precio) as total
    FROM citas c
    JOIN citasServicios cs ON c.id = cs.citaId
    JOIN servicios s ON cs.servicioId = s.id
    GROUP BY c.fecha ORDER BY c.fecha ASC";
$res_hist = mysqli_query($db, $sql_historico);
$fechas = []; $ingresos_hist = [];
while($row = mysqli_fetch_assoc($res_hist)) {
    $fecha_obj = date_create($row['fecha']);
    $fechas[] = date_format($fecha_obj, 'd-M');
    $ingresos_hist[] = $row['total'];
}

// D) Top Servicios Histórico (Gráfica Dona)
$sql_top = "
    SELECT s.nombre, COUNT(cs.id) as cant
    FROM servicios s JOIN citasServicios cs ON s.id = cs.servicioId
    GROUP BY s.nombre ORDER BY cant DESC LIMIT 5";
$res_top = mysqli_query($db, $sql_top);
$serv_nombres = []; $serv_cant = [];
while($row = mysqli_fetch_assoc($res_top)) {
    $serv_nombres[] = $row['nombre'];
    $serv_cant[] = $row['cant'];
}
$top_servicio_nombre = $serv_nombres[0] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Diario</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-main: #121212; /* Fondo principal total */
            --card-bg: #1e1e1e; /* Color de las tarjetas */
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --accent-blue: #3498db;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Estructura principal */
        .contenedor-reporte { display: flex; flex: 1; overflow: hidden; }
        
        /* Panel Izquierdo (Imagen) */
        .panel-imagen {
            width: 35%;
            background-image: url('../../build/img/barber-bg.jpg'); 
            background-size: cover; background-position: center;
            position: relative;
        }
        .panel-imagen::after { content: ''; position: absolute; inset:0; background: rgba(0,0,0,0.5); }

        /* Panel Derecho (Datos) - SIN FONDO NEGRO PESADO */
        .panel-datos {
            width: 65%;
            padding: 30px;
            overflow-y: auto;
            background: transparent; /* quitamos el fondo sólido */
        }

        h2.titulo-seccion { font-weight: 800; margin-bottom: 20px; font-size: 24px; }

        /* KPIs (Tarjetas Superiores) */
        .grid-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .card-kpi {
            background: var(--card-bg);
            border-radius: 16px; padding: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .card-kpi h3 { color: var(--text-secondary); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .card-kpi .valor { font-size: 32px; font-weight: 800; color: var(--accent-blue); }
        .valor.texto { font-size: 20px; color: #fff; line-height: 1.2; }

        /* Gráficas */
        .grid-graficas { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; height: 300px; margin-bottom: 30px; }
        .chart-box {
            background: var(--card-bg);
            border-radius: 16px; padding: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
        }

        /* Nueva Tabla de Citas de Hoy */
        .tabla-container {
            background: var(--card-bg);
            border-radius: 16px; padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .tabla-hoy { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .tabla-hoy th { text-align: left; color: var(--text-secondary); font-size: 13px; text-transform: uppercase; padding-bottom: 10px; border-bottom: 1px solid #333; }
        .tabla-hoy td { padding: 15px 0; border-bottom: 1px solid #2a2a2a; font-size: 15px; }
        .tabla-hoy .precio { font-weight: 700; color: var(--accent-blue); }
        .sin-citas { color: var(--text-secondary); font-style: italic; padding: 20px 0; text-align: center;}

        @media (max-width: 1024px) {
            body { overflow: auto; }
            .contenedor-reporte { flex-direction: column; }
            .panel-imagen { width: 100%; height: 250px; }
            .panel-datos { width: 100%; }
            .grid-kpis { grid-template-columns: 1fr; }
            .grid-graficas { grid-template-columns: 1fr; height: auto; }
            .chart-box { height: 300px; }
        }
    </style>
</head>
<body>

    <div class="contenedor-reporte">
        <div class="panel-imagen"></div>
        
        <div class="panel-datos">
            <h2 class="titulo-seccion">Resumen del Día (<?php echo date('d-m-Y'); ?>)</h2>

            <div class="grid-kpis">
                <div class="card-kpi">
                    <h3>Ventas Hoy</h3>
                    <div class="valor">$ <?php echo number_format($ventas_hoy, 0); ?></div>
                </div>
                <div class="card-kpi">
                    <h3>Citas Hoy</h3>
                    <div class="valor" style="color: #2ecc71;"><?php echo $citas_hoy; ?></div>
                </div>
                <div class="card-kpi">
                    <h3>Top Histórico</h3>
                    <div class="valor texto"><?php echo $top_servicio_nombre; ?></div>
                </div>
            </div>

            <h2 class="titulo-seccion" style="font-size: 18px; color: var(--text-secondary);">Contexto Histórico</h2>
            <div class="grid-graficas">
                <div class="chart-box">
                    <canvas id="chartLinea"></canvas>
                </div>
                <div class="chart-box">
                    <canvas id="chartDona"></canvas>
                </div>
            </div>

            <h2 class="titulo-seccion">Detalle de Citas de Hoy</h2>
            <div class="tabla-container">
                <?php if(mysqli_num_rows($resultado_lista_hoy) > 0): ?>
                    <table class="tabla-hoy">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Servicios</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cita = mysqli_fetch_assoc($resultado_lista_hoy)): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo $cita['hora_fmt']; ?></td>
                                <td><?php echo $cita['cliente']; ?></td>
                                <td style="color: var(--text-secondary); font-size: 14px;"><?php echo $cita['servicios_lista']; ?></td>
                                <td class="precio">$ <?php echo number_format($cita['total_cita'], 0); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="sin-citas">No hay citas agendadas para hoy.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        // Configuración Dark Mode ChartJS
        Chart.defaults.color = '#a0a0a0';
        Chart.defaults.borderColor = '#2a2a2a';
        Chart.defaults.font.family = 'Poppins';

        // Gráfica Línea Histórica
        new Chart(document.getElementById('chartLinea'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($fechas); ?>,
                datasets: [{
                    label: 'Ingresos',
                    data: <?php echo json_encode($ingresos_hist); ?>,
                    borderColor: '#3498db', backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3, fill: true, tension: 0.4, pointRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: {display:false}, title: {display:true, text:'Tendencia de Ingresos', font:{size:16}} },
                scales: { y: {beginAtZero:true, grid:{color:'#2a2a2a'}} }
            }
        });

        // Gráfica Dona Top Servicios
        new Chart(document.getElementById('chartDona'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($serv_nombres); ?>,
                datasets: [{
                    data: <?php echo json_encode($serv_cant); ?>,
                    backgroundColor: ['#3498db', '#e74c3c', '#f1c40f', '#2ecc71', '#9b59b6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: {position:'right', labels:{boxWidth:12, color:'#fff'}} }
            }
        });
    </script>
</body>
</html>