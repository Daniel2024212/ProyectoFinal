<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Sigue los pasos para agendar tu servicio</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">1. Servicios</button>
        <button type="button" data-paso="2">2. Información</button>
        <button type="button" data-paso="3">3. Resumen</button>
        <button type="button" data-paso="4">4. Pago</button>
        <button type="button" data-paso="5">5. Valoración</button>
    </nav>

    <div id="paso-1" class="seccion">
        <h2>Elige tus Servicios</h2>
        <p class="text-center">Selecciona los servicios que deseas</p>
        <div id="servicios" class="listado-servicios"></div>
    </div>

    <div id="paso-2" class="seccion">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Elige la fecha y hora de tu cita</p>

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

    <div id="paso-4" class="seccion">
        <h2>Realizar Pago</h2>
        <p class="text-center">Selecciona tu método de pago</p>
        
        <div class="formulario" style="max-width: 60rem; margin: 0 auto;">
            <div class="campo">
                <label for="metodo-pago">Método de Pago</label>
                <select id="metodo-pago">
                    <option value="" disabled selected>-- Seleccione --</option>
                    <option value="efectivo">Efectivo (En local)</option>
                    <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>

            <div class="campo" style="justify-content: space-between; background-color: #fff; padding: 1rem; border-radius: 1rem;">
                <label style="color: #000; font-weight: bold;">Total a Pagar:</label>
                <p id="pago-total" style="font-size: 2.2rem; font-weight: 900; margin: 0; color: #0da6f3;">$0.00</p>
            </div>

            <div id="smart-button-container" style="margin-top: 2rem;">
                </div>
        </div>

        <div class="alinear-centro" style="margin-top: 3rem; text-align: center;">
            <button type="button" id="btn-pagar" class="boton">Confirmar Pago</button>
        </div>
    </div>

    <div id="paso-5" class="seccion">
        <h2>Califica el Servicio</h2>
        <p class="text-center">¿Qué te pareció la experiencia?</p>

        <form class="formulario" style="max-width: 60rem; margin: 0 auto;">
            <div class="campo">
                <label for="estrellas">Puntuación</label>
                <select id="estrellas">
                    <option value="5">⭐⭐⭐⭐⭐ (Excelente)</option>
                    <option value="4">⭐⭐⭐⭐ (Muy bien)</option>
                    <option value="3">⭐⭐⭐ (Regular)</option>
                    <option value="2">⭐⭐ (Malo)</option>
                    <option value="1">⭐ (Pésimo)</option>
                </select>
            </div>

            <div class="campo">
                <label for="comentario">Comentario</label>
                <textarea id="comentario" rows="5" placeholder="Escribe tu opinión aquí..."></textarea>
            </div>

            <div class="alinear-centro" style="text-align: center;">
                <button type="button" id="btn-valorar" class="boton">Enviar Reseña</button>
            </div>
        </form>
    </div>

    <div class="paginacion">
        <button id="anterior" class="boton">&laquo; Anterior</button>
        <button id="siguiente" class="boton">Siguiente &raquo;</button>
    </div>
</div>

<?php 
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='build/js/app.js'></script>
    ";
?>