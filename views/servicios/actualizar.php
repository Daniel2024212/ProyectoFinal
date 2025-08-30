<h2 class="nombre-pagina">Actualizar Servicios</h2>
<p class="descripcion-pagina">Modifica los valores del formulario</p>

<?php
    include_once __DIR__ . '/../templates/barra.php';
    include_once __DIR__ . '/../templates/alertas.php';
?>

<form class="formulario" method="POST"> <!-- Se elimina el acction porque depués se pierde la referencia del id a actualizar (?id=) -->

    <?php include_once __DIR__ . '/formulario.php'; ?>

    <input type="submit" class="boton" value="Actualizar">
</form>