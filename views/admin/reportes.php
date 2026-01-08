<?php
// --- LÓGICA PHP (Igual que antes, solo obtenemos los datos) ---
include '../includes/database.php';

// 1. Datos de Ingresos
$sql_ingresos = "
    SELECT c.fecha, SUM(s.precio) as total_ingresos
    FROM citas c
    LEFT JOIN citasServicios cs ON c.id = cs.citaId
    LEFT JOIN servicios s ON cs.servicioId = s.id
    WHERE c.fecha <= CURDATE() 
    GROUP BY c.fecha ORDER BY c.fecha DESC LIMIT 7"; // Limitado a 7 para que se vea limpio
$resultado_ingresos = mysqli_query($db, $sql_ingresos);

$fechas = []; $ingresos = [];
while($row = mysqli_fetch_assoc($resultado_ingresos)) {
    $fechas[] = date('d-M', strtotime($row['fecha'])); // Formato corto
    $ingresos[] = $row['total_ingresos'];
}

// 2. Datos de Servicios (Pastel)
$sql_servicios = "
    SELECT s.nombre, COUNT(cs.id) as cantidad
    FROM servicios s
    LEFT JOIN citasServicios cs ON s.id = cs.servicioId
    GROUP BY s.nombre ORDER BY cantidad DESC LIMIT 5";
$resultado_servicios = mysqli_query($db, $sql_servicios);

$servicios_nombres = []; $servicios_cantidad = [];
while($row = mysqli_fetch_assoc($resultado_servicios)) {
    $servicios_nombres[] = $row['nombre'];
    $servicios_cantidad[] = $row['cantidad'];
}

// 3. KPIs Rápidos (Totales generales)
$total_ventas_hoy = $ingresos[0] ?? 0;
$top_servicio = $servicios_nombres[0] ?? 'Sin datos';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- ESTILOS GENERALES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex; /* Layout dividido */
            overflow: hidden; /* Evita scroll en el body principal */
        }

        /* --- IZQUIERDA: IMAGEN (40%) --- */
        .panel-imagen {
            flex: 0 0 40%;
            background-image: url('../img/barber-bg.jpg'); /* ASEGÚRATE DE TENER ESTA IMAGEN */
            background-size: cover;
            background-position: center;
            position: relative;
        }
        /* Filtro oscuro sobre la imagen para que se vea elegante */
        .panel-imagen::after {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.3);
        }

        /* --- DERECHA: DATOS (60%) --- */
        .panel-datos {
            flex: 1;
            background-color: #f4f7fc;
            padding: 30px;
            overflow-y: auto; /* Scroll solo en los datos si es necesario */
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* --- TARJETAS SUPERIORES --- */
        .grid-kpis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 10px;
        }

        .card {
            background: black;
            border-radius: 20px; /* Bordes muy redondeados */
            padding: 25px 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 150px;
        }

        .card h3 {
            color: #95a5a6;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .card .valor {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 900; /* Letra extra gruesa */
            line-height: 1.1;
        }

        /* Estilo específico para la tarjeta de texto largo */
        .valor.texto-largo { font-size: 22px; }

        /* --- GRÁFICAS --- */
        .grid-graficas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            height: 100%;
        }

        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsive para móviles */
        @media (max-width: 900px) {
            body { flex-direction: column; overflow: auto; }
            .panel-imagen { height: 200px; flex: none; }
            .grid-kpis { grid-template-columns: 1fr; }
            .grid-graficas { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="panel-imagen">
        </div>

    <div class="panel-datos">
        
        <div class="grid-kpis">
            <div class="card">
                <h3>Ventas Hoy</h3>
                <div class="valor">$ <?php echo number_format($total_ventas_hoy, 0); ?></div>
            </div>

            <div class="card">
                <h3>Fecha</h3>
                <div class="valor"><?php echo date('d M'); ?></div>
            </div>

            <div class="card">
                <h3>Top Servicio</h3>
                <div class="valor texto-largo">
                    <?php echo $top_servicio; ?>
                </div>
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

    <script>
        // Configuración común para que se vea limpio
        Chart.defaults.font.family = 'Poppins';
        Chart.defaults.color = '#666';

        // 1. Gráfica de Ingresos (Barras azules)
        const ctx1 = document.getElementById('chartIngresos').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_reverse($fechas)); ?>,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: <?php echo json_encode(array_reverse($ingresos)); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5, // Bordes redondeados en las barras
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, title: { display: true, text: 'Ingresos Semanales' } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Gráfica de Servicios (Dona de colores)
        const ctx2 = document.getElementById('chartServicios').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($servicios_nombres); ?>,
                datasets: [{
                    data: <?php echo json_encode($servicios_cantidad); ?>,
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9b59b6'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%', // Hace el agujero de la dona más grande
                plugins: { 
                    legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true } } 
                }
            }
        });
    </script>
</body>
</html>