<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">Servicios</button>
        <button type="button" data-paso="2">Información Cita</button>
        <button type="button" data-paso="3">Resumen</button>
    </nav>

    <form class="formulario" method="POST" action="guardar_cita.php">
        <div id="paso-1" class="seccion">
            <h2>Servicios</h2>
            <p class="text-center">Elige tus servicios a continuación</p>
            <div id="servicios" class="listado-servicios">
                <!-- Aquí puedes renderizar los servicios desde PHP -->
                <label><input type="radio" name="servicio_id" value="1"> Corte de cabello</label>
                <label><input type="radio" name="servicio_id" value="2"> Manicure</label>
                <label><input type="radio" name="servicio_id" value="3"> Masaje</label>
            </div>
        </div>

        <div id="paso-2" class="seccion">
            <h2>Tus Datos y Cita</h2>
            <p class="text-center">Coloca tus datos y fecha de tu cita</p>

            <div class="campo">
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" name="nombre" placeholder="Tu Nombre">
            </div>

            <div class="campo">
                <label for="fecha">Fecha</label>
                <input id="fecha" type="date" name="fecha" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>

            <div class="campo">
                <label for="hora">Hora</label>
                <input id="hora" type="time" name="hora">
            </div>

            <input type="hidden" name="usuarioid" value="<?php echo $id; ?>">
        </div>

        <div id="paso-3" class="seccion contenido-resumen">
            <h2>Resumen</h2>
            <p class="text-center">Verifica que la información sea correcta</p>
            <!-- Aquí puedes mostrar un resumen con JS -->
        </div>

        <div class="paginacion">
            <button id="anterior" class="boton" type="button">&laquo; Anterior</button>
            <button id="siguiente" class="boton" type="submit">Guardar Cita &raquo;</button>
        </div>
    </form>
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