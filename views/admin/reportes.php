<?php
// 1. INCLUIR CONEXIÓN (Ajusta la ruta si tu carpeta es diferente)
include '../includes/database.php';

// 2. SEGURIDAD (Opcional pero recomendado)
// session_start();
// if(!isset($_SESSION['admin'])) { header('Location: /login.php'); }

// 3. LÓGICA DE REPORTES (El código PHP que te di antes)
$sql_ingresos = "
    SELECT 
        c.fecha, 
        COUNT(DISTINCT c.id) as total_citas, 
        SUM(s.precio) as total_ingresos
    FROM citas c
    LEFT JOIN citasServicios cs ON c.id = cs.citaId
    LEFT JOIN servicios s ON cs.servicioId = s.id
    WHERE c.fecha <= CURDATE() 
    GROUP BY c.fecha 
    ORDER BY c.fecha DESC 
    LIMIT 10";

$resultado_ingresos = mysqli_query($db, $sql_ingresos);

$fechas = [];
$ingresos = [];
$total_citas_hoy = 0;
$hoy = date('Y-m-d');

while($row = mysqli_fetch_assoc($resultado_ingresos)) {
    $fechas[] = $row['fecha']; 
    $ingresos[] = $row['total_ingresos'] ?? 0;
    
    if($row['fecha'] === $hoy) {
        $total_citas_hoy = $row['total_citas'];
    }
}

$sql_servicios = "
    SELECT s.nombre, COUNT(cs.id) as cantidad
    FROM servicios s
    LEFT JOIN citasServicios cs ON s.id = cs.servicioId
    GROUP BY s.nombre
    ORDER BY cantidad DESC LIMIT 5";

$resultado_servicios = mysqli_query($db, $sql_servicios);

$servicios_nombres = [];
$servicios_cantidad = [];

while($row = mysqli_fetch_assoc($resultado_servicios)) {
    $servicios_nombres[] = $row['nombre'];
    $servicios_cantidad[] = $row['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Pegar aquí los estilos CSS que te di en la respuesta anterior */
        :root { --primary: #4a90e2; --bg: #f4f6f9; --card: #ffffff; }
        body { font-family: sans-serif; background: var(--bg); padding: 20px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;}
        .card { background: var(--card); padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .charts-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media(max-width:768px){ .charts-container{ grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>📊 Panel de Reportes</h1>
    
    <div class="kpi-grid">
        <div class="card">
            <h3>Ingresos (Día Reciente)</h3>
            <h2>$ <?php echo number_format($ingresos[0] ?? 0, 2); ?></h2>
        </div>
        <div class="card">
            <h3>Citas Hoy</h3>
            <h2><?php echo $total_citas_hoy; ?></h2>
        </div>
        <div class="card">
            <h3>Top Servicio</h3>
            <h2><?php echo $servicios_nombres[0] ?? 'N/A'; ?></h2>
        </div>
    </div>

    <div class="charts-container">
        <div class="card">
            <canvas id="graficaIngresos"></canvas>
        </div>
        <div class="card">
            <canvas id="graficaServicios"></canvas>
        </div>
    </div>

    <script>
        const ctx1 = document.getElementById('graficaIngresos');
        const fechasJS = <?php echo json_encode(array_reverse($fechas)); ?>;
        const ingresosJS = <?php echo json_encode(array_reverse($ingresos)); ?>;

        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: fechasJS,
                datasets: [{ label: 'Ingresos ($)', data: ingresosJS, backgroundColor: '#36A2EB' }]
            }
        });

        const ctx2 = document.getElementById('graficaServicios');
        const servNames = <?php echo json_encode($servicios_nombres); ?>;
        const servCant = <?php echo json_encode($servicios_cantidad); ?>;

        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: servNames,
                datasets: [{ data: servCant, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'] }]
            }
        });
    </script>
</body>
</html>