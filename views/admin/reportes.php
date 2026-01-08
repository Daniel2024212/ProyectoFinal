<?php
    include_once __DIR__ . '/../templates/barra.php';
?>

<h2 class="nombre-pagina">Reportes de Ventas</h2>
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
        <p class="precio" id="total-ingresos">$0.00</p>
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
        // Seleccionar inputs
        const fechaInicio = document.querySelector('#fecha_inicio');
        const fechaFin = document.querySelector('#fecha_fin');

        // Cargar datos al abrir la página
        consultarAPI();

        // Recargar datos cuando cambian las fechas
        fechaInicio.addEventListener('change', consultarAPI);
        fechaFin.addEventListener('change', consultarAPI);
    }

    let myChart = null; // Variable global para guardar la instancia de la gráfica

    async function consultarAPI() {
        const fechaInicio = document.querySelector('#fecha_inicio').value;
        const fechaFin = document.querySelector('#fecha_fin').value;

        // Llamada a la API creada en el Controlador
        const url = `/api/reportes?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

        try {
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            
            mostrarDatos(resultado);
        } catch (error) {
            console.error('Error al consultar la API:', error);
        }
    }

    function mostrarDatos(datos) {
        // 1. CALCULAR TOTALES
        let totalIngresos = 0;
        let totalCitas = 0;

        // Arrays para la gráfica
        const labels = [];
        const values = [];

        datos.forEach(dato => {
            // Convertir a números para sumar correctamente
            // Asegúrate que tu API devuelve 'ingreso' y 'cantidad' (o ajusta estos nombres)
            const ingresoDia = parseFloat(dato.ingreso); 
            const cantidadDia = parseInt(dato.cantidad);

            totalIngresos += ingresoDia;
            totalCitas += cantidadDia;

            labels.push(dato.dia); // Eje X (Fechas)
            values.push(ingresoDia); // Eje Y (Dinero)
        });

        // 2. ACTUALIZAR HTML DE LAS TARJETAS
        document.querySelector('#total-ingresos').textContent = `$ ${totalIngresos.toLocaleString('en-US')}`;
        document.querySelector('#total-citas').textContent = totalCitas;

        // 3. RENDERIZAR GRÁFICA
        const ctx = document.getElementById('grafica-ingresos').getContext('2d');

        // Si ya existe una gráfica, destruirla para crear la nueva
        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'bar', // Tipo de gráfica (bar, line, pie)
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos por Día ($)',
                    data: values,
                    backgroundColor: 'rgba(13, 166, 243, 0.6)', // Azul del salón
                    borderColor: 'rgba(13, 166, 243, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    /* Estilos para las tarjetas de resumen */
    .reporte-resumen {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    .card-reporte {
        background-color: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        border-top: 5px solid #0da6f3; /* Borde superior azul */
    }
    .card-reporte h3 {
        margin: 0;
        color: #666;
        text-transform: uppercase;
        font-size: 1.2rem;
    }
    .card-reporte .precio, 
    .card-reporte .cantidad {
        font-size: 3rem;
        font-weight: 900;
        color: #0da6f3;
        margin: 1rem 0 0 0;
    }

    /* Contenedor de la gráfica */
    .grafica-contenedor {
        background-color: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: 400px; /* Altura fija para la gráfica */
        margin-bottom: 5rem;
    }

    /* Ajuste del formulario */
    .formulario .campo {
        margin-bottom: 1rem;
    }
</style>