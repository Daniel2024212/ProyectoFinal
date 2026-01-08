<div class="barra">
    <p>Hola: <span><?php echo $nombre ?? ''; ?></span></p>
    <a href="/logout" class="boton">Cerrar Sesión</a>
</div>

<?php if(isset($_SESSION['admin'])): ?>
    <div class="barra-servicios">
        <a class="boton" href="/cita">Agendar</a>
        <a class="boton" href="/admin">Ver Citas</a>
        <a class="boton" href="/servicios">Ver Servicios</a>
        <a class="boton" href="/servicios/crear">Nuevo Servicio</a>
        <a class="boton" href="/admin/reportes">Ver Reportes</a>
    </div>
<?php endif; ?>