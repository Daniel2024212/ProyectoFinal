<h1 class="nombre-pagina">Crear Nueva Cita</h1>
<p class="descripcion-pagina">Sigue los pasos para agendar tu servicio</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>

<div id="app">
    <nav class="tabs">
        <button class="actual" type="button" data-paso="1">1. Servicios</button>
        <button type="button" data-paso="2">2. Información</button>
        <button type="button" data-paso="3">3. Resumen</button>
        <button type="button" data-paso="4">4. Pago</button>
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

<?php 
    $script = "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script src='build/js/app.js'></script>
    ";
?>