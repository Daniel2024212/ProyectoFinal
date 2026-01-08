<h2 class="nombre-pagina">Panel de Reportes</h2>
<p class="descripcion-pagina">Resumen de ingresos y citas del salón</p>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="busqueda">
    <form id="filtro-reportes" class="formulario">
        <div class="campo">
            <label for="fecha_inicio">Desde:</label>
            <input 
                type="date" 
                id="fecha_inicio" 
                name="fecha_inicio" 
                value="<?php echo date('Y-m-01'); ?>"
            />
        </div>

        <div class="campo">
            <label for="fecha_fin">Hasta:</label>
            <input 
                type="date" 
                id="fecha_fin" 
                name="fecha_fin" 
                value="<?php echo date('Y-m-d'); ?>"
            />
        </div>
    </form>
</div>

<div class="reporte-resumen">
    <div class="card-reporte">
        <h3>Total Ingresos</h3>
        <p class="precio" id="total-ingresos">$0</p>
    </div>
    
    <div class="card-reporte">
        <h3>Total Citas</h3>
        <p id="total-citas">0</p>
    </div>
</div>

<div class="grafica-contenedor">
    <canvas id="grafica-ingresos"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        iniciarAppReportes();
    });

    function iniciarAppReportes() {
        const fechaInicio = document.querySelector('#fecha_inicio');
        const fechaFin = document.querySelector('#fecha_fin');

        // Cargar datos iniciales
        consultarAPI();

        // Escuchar cambios en las fechas
        fechaInicio.addEventListener('change', consultarAPI);
        fechaFin.addEventListener('change', consultarAPI);
    }

    let myChart = null; // Variable global para la gráfica

    async function consultarAPI() {
        const fechaInicio = document.querySelector('#fecha_inicio').value;
        const fechaFin = document.querySelector('#fecha_fin').value;

        // NOTA: Esta URL debe coincidir con la que definiste en el Router.php
        // Ejemplo: /api/reportes?fecha_inicio=...
        const url = `/api/reportes?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

        try {
            const resultado = await fetch(url);
            const datos = await resultado.json();
            
            mostrarDatos(datos);
        } catch (error) {
            console.log(error);
        }
    }

    function mostrarDatos(datos) {
        // 1. Actualizar Tarjetas
        // Calcular totales sumando los datos recibidos
        let totalIngresos = 0;
        let totalCitas = 0;

        // Preparar arrays para la gráfica
        const labels = [];
        const dataIngresos = [];

        datos.forEach(dato => {
            totalIngresos += parseFloat(dato.ingreso); // Asumiendo que tu API devuelve 'ingreso'
            totalCitas += parseInt(dato.cantidad);     // Asumiendo que tu API devuelve 'cantidad'
            
            labels.push(dato.dia);      // Asumiendo que tu API devuelve 'dia'
            dataIngresos.push(dato.ingreso);
        });

        document.querySelector('#total-ingresos').textContent = `$ ${totalIngresos}`;
        document.querySelector('#total-citas').textContent = totalCitas;

        // 2. Renderizar Gráfica
        const ctx = document.getElementById('grafica-ingresos').getContext('2d');

        // Si ya existe una gráfica previa, la destruimos para crear la nueva
        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos',
                    data: dataIngresos,
                    backgroundColor: '#0da6f3', // Color de las barras (Azul)
                    borderColor: '#0da6f3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>

<style>
    .reporte-resumen {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
        margin-top: 2rem;
    }
    .card-reporte {
        background-color: #fff;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    .card-reporte h3 {
        margin: 0;
        color: #333;
    }
    .card-reporte p {
        font-size: 2.5rem;
        font-weight: bold;
        color: #0da6f3;
        margin: 1rem 0 0 0;
    }
    .grafica-contenedor {
        background-color: #fff;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .formulario .campo {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
</style>