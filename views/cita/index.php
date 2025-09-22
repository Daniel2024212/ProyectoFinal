<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Elige tus servicios y completa el proceso</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div id="app">
    <!-- Barra de pasos -->
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">1. Servicios</button>
        <button type="button" data-paso="2">2. Pago</button>
        <button type="button" data-paso="3">3. Información</button>
        <button type="button" data-paso="4">4. Resumen</button>
        <button type="button" data-paso="5">5. Valoración</button>
    </nav>

    <!-- Paso 1 -->
    <div id="paso-1" class="seccion">
        <h2>Servicios</h2>
        <p class="text-center">Elige tus servicios a continuación</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <!-- Paso 2: Pago -->
    <div id="paso-2" class="seccion">
        <h2>Pago en Efectivo</h2>
        <p class="text-center">El total se calcula automáticamente.</p>

        <div class="campo">
            <label>Total a pagar</label>
            <p id="pago-total" style="font-weight:bold;font-size:1.2rem;">$0.00</p>
        </div>

        <button type="button" id="btn-pagar" class="boton">Confirmar Pago</button>
        <p id="pago-resultado" class="mensaje"></p>
    </div>


    <!-- Paso 3: Datos de la cita -->
    <div id="paso-3" class="seccion">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Coloca tus datos y fehca de tu cita</p>

        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre</label>
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
            <input type="hidden" id="id" value="<?php echo $id; ?>">
        </form>
    </div>

    <!-- Paso 4: Resumen -->
    <div id="paso-4" class="seccion contenido-resumen">
        <h2>Resumen</h2>
        <p class="text-center">Verifica que la información sea correcta antes de confirmar</p>
    </div>

    <!-- Paso 5: Valoración -->
    <div id="paso-5" class="seccion">
        <h2>Valoración</h2>
        <p class="text-center">Tu opinión es importante. Valora tu experiencia una vez terminada la cita.</p>

        <form id="form-valoracion" class="formulario">
            <div class="campo">
                <label for="valoracion-estrellas">Calificación</label>
                <select id="valoracion-estrellas">
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>
            </div>

            <div class="campo">
                <label for="valoracion-comentario">Comentario</label>
                <textarea id="valoracion-comentario" rows="4" placeholder="Escribe tu comentario"></textarea>
            </div>

            <button type="button" id="btn-valoracion" class="boton">Enviar Valoración</button>
            <p id="valoracion-resultado" class="mensaje"></p>
        </form>
    </div>

    <!-- Navegación -->
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