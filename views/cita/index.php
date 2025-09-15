<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Elige tus servicios y coloca tus datos</p>

<?php
include_once __DIR__ . '/../templates/barra.php';
?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">Servicios</button>
        <button type="button" data-paso="2">Información Cita</button>
        <button type="button" data-paso="3">Pagos</button>
        <button type="button" data-paso="4">Resumen</button>
        <button type="button" data-paso="5">Valoración</button>
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
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" name="nombre" placeholder="Tu Nombre">
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

    <div id="paso-3" class="seccion">
        <h1>Pagos</h1>
        <form id="form-pago">
            <label>Monto a pagar
                <input type="number" step="0.01" id="pago-monto" value="<?php echo s($precio); ?>" readonly>
            </label>

            <label>Método de pago
                <select id="pago-metodo" required>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="efectivo">Efectivo</option>
                </select>
            </label>

            <button type="button" id="btn-pagar" class="boton">Realizar Pago</button>
            <p id="pago-resultado" class="mensaje"></p>
        </form>
    </div>


    <div id="paso-4" class="seccion contenido-resumen">
        <h2>Resumen</h2>
        <p class="text-center">Verifica que la información sea correacta</p>
    </div>

    <div id="paso-5" class="seccion">
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