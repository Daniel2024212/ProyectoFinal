<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<h2 class="nombre-pagina">Panel de Reportes</h2>
<p class="descripcion-pagina">Resumen de ingresos y citas del salón</p>

<div class="barra-servicios">
    <a class="boton" href="/admin">Volver al Panel</a>
</div>

<h3 class="descripcion-pagina">Filtrar por Fecha</h3>
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
        <p class="cantidad" id="total-citas">0</p>
    </div>
</div>

<div class="grafica-contenedor">
    <canvas id="grafica-ingresos"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        iniciarAppReportes();
    });

    function iniciarAppReportes() {
        const fechaInicio = document.querySelector('#fecha_inicio');
        const fechaFin = document.querySelector('#fecha_fin');

        consultarAPI(); // Carga inicial

        fechaInicio.addEventListener('change', consultarAPI);
        fechaFin.addEventListener('change', consultarAPI);
    }

    let myChart = null;

    async function consultarAPI() {
        const fechaInicio = document.querySelector('#fecha_inicio').value;
        const fechaFin = document.querySelector('#fecha_fin').value;

        const url = `/api/reportes?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

        try {
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            mostrarDatos(resultado);
        } catch (error) {
            console.error(error);
        }
    }

    function mostrarDatos(datos) {
        let totalIngresos = 0;
        let totalCitas = 0;
        const labels = [];
        const values = [];

        datos.forEach(dato => {
            const ingreso = parseFloat(dato.ingreso);
            const cantidad = parseInt(dato.cantidad);
            
            totalIngresos += ingreso;
            totalCitas += cantidad;

            labels.push(dato.dia);
            values.push(ingreso);
        });

        // Actualizar HTML
        document.querySelector('#total-ingresos').textContent = `$ ${totalIngresos.toLocaleString()}`;
        document.querySelector('#total-citas').textContent = totalCitas;

        // Gráfica
        const ctx = document.getElementById('grafica-ingresos').getContext('2d');
        if (myChart) myChart.destroy();

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: values,
                    backgroundColor: '#0da6f3',
                    borderColor: '#0da6f3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
</script>

<style>
    .reporte-resumen {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin: 2rem 0;
    }
    .card-reporte {
        background-color: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    .card-reporte p {
        font-size: 2.5rem;
        font-weight: bold;
        color: #0da6f3;
        margin: 1rem 0 0 0;
    }
    .grafica-contenedor {
        height: 400px;
        background: white;
        padding: 1rem;
        border-radius: 1rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
</style>