<di class="campo">
    <label for="nombre">Nombre</label>
    <input type="text" name="nombre" id="nombre" placeholder="Nombre Servicio" value="<?php echo s($servicio->nombre); ?>">
</di>

<di class="campo">
    <label for="precio">Precio</label>
    <input type="number" name="precio" id="precio" placeholder="Precio Servicio" min="0" value="<?php echo s($servicio->precio); ?>">
</di>