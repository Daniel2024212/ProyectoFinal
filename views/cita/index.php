<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Sigue los pasos para agendar tu servicio</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">1. Servicios</button>
        <button type="button" data-paso="2">2. Información</button>
        <button type="button" data-paso="3">3. Resumen</button>
    </nav>

    <div id="paso-1" class="seccion">
        <h2>Elige tus Servicios</h2>
        <p class="text-center">Selecciona los servicios que deseas</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <div id="paso-2" class="seccion">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Elige la fecha y hora de tu cita entre las 10:00 y las 18:00</p>

        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" placeholder="Tu Nombre" value="<?php echo s($nombre); ?>" disabled>
            </div>

            <div class="campo">
                <label for="fecha">Fecha</label>
                <input id="fecha" type="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>

            <div class="campo">
                <label for="hora">Hora</label>
                <input id="hora" type="time">
            </div>
            <input type="hidden" id="id" value="<?php echo $id; ?>">
        </form>
    </div>

    <div id="paso-3" class="seccion contenido-resumen">
        <h2>Resumen de Cita</h2>
        <p class="text-center">Verifica tu información antes de confirmar</p>
    </div>

    <div class="paginacion">
        <button id="anterior" class="boton">&laquo; Anterior</button>
        <button id="siguiente" class="boton">Siguiente &raquo;</button>
    </div>
</div>

<div class="seccion-historial">
    <h2>Mis Citas Pasadas</h2>
    
    <?php if(isset($citas) && count($citas) > 0): ?>
        <ul class="listado-citas-cliente">
            <?php foreach($citas as $cita): ?>
                <li class="cita-item">
                    <div class="info-cita">
                        <?php 
                            $fechaCita = is_object($cita) ? $cita->fecha : $cita['fecha'];
                            $horaCita = is_object($cita) ? $cita->hora : $cita['hora'];
                            $idCita = is_object($cita) ? $cita->id : $cita['id'];
                        ?>
                        <p>Fecha: <span><?php echo $fechaCita; ?></span></p>
                        <p>Hora: <span><?php echo $horaCita; ?></span></p>
                    </div>
                    
                    <div class="acciones-cita">
                        <a href="/valorar?id=<?php echo $idCita; ?>" class="boton-valorar">
                            ★ Calificar
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-center">No tienes citas registradas para calificar.</p>
    <?php endif; ?>
</div>

<style>
    /* Separación del historial */
    .seccion-historial {
        margin-top: 5rem;
        padding-top: 3rem;
        border-top: 1px solid #333;
    }

    /* Grid de citas */
    .listado-citas-cliente {
        list-style: none;
        padding: 0;
        display: grid;
        gap: 2rem;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
    
    /* Tarjeta de cada cita */
    .cita-item {
        background-color: #1a1b1c;
        padding: 2rem;
        border-radius: 1rem;
        border: 1px solid #333;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }

    .info-cita p { margin: 0.5rem 0; color: #fff; }
    .info-cita span { font-weight: bold; color: #0da6f3; }

    /* Botón Amarillo */
    .boton-valorar {
        background-color: #ffc700;
        color: #1a1a1a;
        padding: 1rem 2rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.2rem;
        display: inline-block;
        transition: 0.3s;
    }
    .boton-valorar:hover {
        background-color: #e0b000;
        cursor: pointer;
        transform: scale(1.05);
    }
</style>

<?php
$script = "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'></script>
    <script src='build/js/app.js'></script>
    <script src='build/js/mapa.js'></script>
";
?>