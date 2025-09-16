<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<?php
include_once __DIR__ . '/../templates/barra.php';
?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">Servicios</button>
        <button type="button" data-paso="2">Información Cita</button>
        <button type="button" data-paso="3">Resumen</button>
    </nav>

    <div id="paso-1" class="seccion">
        <h2>Servicios</h2>
        <p class="text-center">Elige tus servicios a continuación</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <div id="paso-2" class="seccion">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Coloca tus datos y fehca de tu cita</p>
        <p class="text-center">Horario de atención de 10 am a 18 pm</p>

        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input id="nombre" type="text" placeholder="Tu Nombre" value="<?php echo s($nombre); ?>" disabled>
            </div> <!-- .campo -->

            <div class="campo">
                <label for="fecha">Fecha</label>
                <input id="fecha" type="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div> <!-- .campo -->

            <div class="campo">
                <label for="hora">Hora</label>
                <input id="hora" type="time">
            </div> <!-- .campo -->
            <input type="hidden" id="id" value="<?php echo $id; ?>" onkeydown="return false">
        </form>
    </div>

    <div id="paso-3" class="seccion contenido-resumen">
        <h2>Resumen</h2>
        <p class="text-center">Verifica que la información sea correacta</p>
    </div>

    <div class="paginacion">
        <button id="anterior" class="boton">&laquo; Anterior</button>
        <button id="siguiente" class="boton">Siguiente &raquo;</button>
    </div>
</div>

<div id="mapa" class="mapa"></div>

<?php
$script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'></script>
        <script src='build/js/app.js'></script>
        <script src='build/js/mapa.js'></script>
    ";
?>