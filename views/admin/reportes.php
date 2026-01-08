<?php
// 1. IMPORTAR CONEXIÓN Y FUNCIONES
include '../includes/database.php';
include '../includes/funciones.php'; // <--- IMPORTANTE: Asegúrate de que aquí está tu barra (o 'templates/barra.php')

// 2. SEGURIDAD (Si la usas)
// session_start();
// isAdmin();

// --- LÓGICA DE DATOS CORREGIDA ---

// CONSULTA 1: Ingresos por fecha (Quitamos el WHERE CURDATE para ver todo el historial de prueba)
$sql_ingresos = "
    SELECT 
        c.fecha, 
        SUM(s.precio) as total_ingresos
    FROM citas c
    INNER JOIN citasServicios cs ON c.id = cs.citaId
    INNER JOIN servicios s ON cs.servicioId = s.id
    GROUP BY c.fecha 
    ORDER BY c.fecha ASC"; // Orden ASC para que la gráfica vaya de izquierda (antiguo) a derecha (nuevo)

$resultado_ingresos = mysqli_query($db, $sql_ingresos);

$fechas = []; 
$ingresos = [];
$total_ventas_historico = 0;

while($row = mysqli_fetch_assoc($resultado_ingresos)) {
    // Formatear fecha para que ocupe menos espacio (ej: 11-Dic)
    $fecha_obj = date_create($row['fecha']);
    $fechas[] = date_format($fecha_obj, 'd-M'); 
    
    $ingresos[] = $row['total_ingresos'];
    $total_ventas_historico += $row['total_ingresos'];
}

// CONSULTA 2: Servicios más vendidos (Top 5 real)
$sql_servicios = "
    SELECT 
        s.nombre, 
        COUNT(cs.id) as cantidad
    FROM servicios s
    LEFT JOIN citasServicios cs ON s.id = cs.servicioId
    GROUP BY s.nombre 
    ORDER BY cantidad DESC 
    LIMIT 5";

$resultado_servicios = mysqli_query($db, $sql_servicios);

$servicios_nombres = []; 
$servicios_cantidad = [];

while($row = mysqli_fetch_assoc($resultado_servicios)) {
    $servicios_nombres[] = $row['nombre'];
    $servicios_cantidad[] = $row['cantidad'];
}

// KPIS RÁPIDOS
$top_servicio = $servicios_nombres[0] ?? 'Sin datos';
$total_dias_registrados = count($fechas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- ESTILOS --- */
        :root {
            --fondo-oscuro: #121212;
            --tarjeta-gris: #1e1e1e;
            --texto-blanco: #ffffff;
            --azul-electrico: #3498db;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--fondo-oscuro);
            color: var(--texto-blanco);
            height: 100vh;
            display: flex;
            flex-direction: column; /* Importante para que la barra quede arriba */
            overflow: hidden; 
        }

        /* Ajuste para la barra de navegación que importas */
        .barra {
            flex-shrink: 0; /* Evita que la barra se aplaste */
            z-index: 1000;
        }

        /* Contenedor principal dividido (Imagen | Datos) */
        .contenedor-reporte {
            display: flex;
            flex: 1; /* Ocupa el resto de la altura */
            overflow: hidden;
        }

        /* --- IZQUIERDA: IMAGEN (40%) --- */
        .panel-imagen {
            width: 40%;
            background-image: url('../img/barber-bg.jpg'); /* Verifica esta ruta */
            background-size: cover;
            background-position: center;
            position: relative;
            border-right: 1px solid #333;
        }
        .panel-imagen::after {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.6);
        }

        /* --- DERECHA: DATOS (60%) --- */
        .panel-datos {
            width: 60%;
            padding: 20px;
            overflow-y: auto; /* Scroll si los datos no caben */
            background-color: var(--fondo-oscuro);
        }

        /* GRID DE TARJETAS */
        .grid-kpis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: var(--tarjeta-gris);
            border-radius: 15px;
            padding: 20px 10px;
            text-align: center;
            border: 1px solid #333;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .card h3 {
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .card .valor {
            font-size: 24px;
            font-weight: 900;
            color: #fff;
        }
        
        .valor.texto-largo { font-size: 18px; line-height: 1.2; }

        /* GRID GRÁFICAS */
        .grid-graficas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            height: 350px; /* Altura fija para gráficas */
        }

        .chart-container {
            background: var(--tarjeta-gris);
            border-radius: 15px;
            padding: 15px;
            border: 1px solid #333;
            position: relative;
            height: 100%; /* Llenar el grid */
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            body { overflow: auto; }
            .contenedor-reporte { flex-direction: column; }
            .panel-imagen { width: 100%; height: 200px; }
            .panel-datos { width: 100%; }
            .grid-kpis { grid-template-columns: 1fr; }
            .grid-graficas { grid-template-columns: 1fr; height: auto; }
            .chart-container { height: 300px; }
        }
    </style>
</head>
<body>

    <div class="contenedor-reporte">
        
        <div class="panel-imagen"></div>

        <div class="panel-datos">
            
            <h2 style="margin-bottom: 20px; font-weight:700;">Panel de Reportes</h2>

            <div class="grid-kpis">
                <div class="card">
                    <h3>Ingresos Totales (Histórico)</h3>
                    <div class="valor">$ <?php echo number_format($total_ventas_historico, 0); ?></div>
                </div>
                <div class="card">
                    <h3>Días con ventas</h3>
                    <div class="valor"><?php echo $total_dias_registrados; ?></div>
                </div>
                <div class="card">
                    <h3>Top Servicio</h3>
                    <div class="valor texto-largo"><?php echo $top_servicio; ?></div>
                </div>
            </div>

            <div class="grid-graficas">
                <div class="chart-container">
                    <canvas id="chartIngresos"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="chartServicios"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuración Global Dark Mode
        Chart.defaults.color = '#cccccc';
        Chart.defaults.borderColor = '#333333';
        Chart.defaults.font.family = 'Poppins';

        // 1. Gráfica Ingresos
        const ctx1 = document.getElementById('chartIngresos').getContext('2d');
        new Chart(ctx1, {
            type: 'line', // Cambié a linea para ver mejor la tendencia histórica
            data: {
                labels: <?php echo json_encode($fechas); ?>,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: <?php echo json_encode($ingresos); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    fill: true,
                    tension: 0.4 // Curva suave
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, title: {display: true, text: 'Historial de Ingresos'} },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 2. Gráfica Servicios
        const ctx2 = document.getElementById('chartServicios').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($servicios_nombres); ?>,
                datasets: [{
                    data: <?php echo json_encode($servicios_cantidad); ?>,
                    backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#2ecc71', '#9b59b6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { 
                    legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } 
                }
            }
        });
    </script>

</body>
</html>