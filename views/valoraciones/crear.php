<h1 class="nombre-pagina">Calificar Servicio</h1>
<p class="descripcion-pagina">Cuéntanos tu experiencia</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<form class="formulario" method="POST">
    
    <div class="campo">
        <label>Calificación:</label>
        <div class="rate">
            <input type="radio" id="star5" name="calificacion" value="5" />
            <label for="star5" title="5 estrellas">★</label>
            <input type="radio" id="star4" name="calificacion" value="4" />
            <label for="star4" title="4 estrellas">★</label>
            <input type="radio" id="star3" name="calificacion" value="3" />
            <label for="star3" title="3 estrellas">★</label>
            <input type="radio" id="star2" name="calificacion" value="2" />
            <label for="star2" title="2 estrellas">★</label>
            <input type="radio" id="star1" name="calificacion" value="1" />
            <label for="star1" title="1 estrella">★</label>
        </div>
    </div>

    <div class="campo">
        <label for="comentario">Comentario:</label>
        <textarea 
            id="comentario" 
            name="comentario" 
            placeholder="Escribe aquí tu opinión..."
        ><?php echo htmlspecialchars($valoracion->comentario ?? ''); ?></textarea>
    </div>

    <input type="submit" class="boton" value="Enviar Reseña">
</form>

<style>
    /* Estilos de las Estrellas Interactivas */
    .rate {
        float: left;
        height: 46px;
        padding: 0 10px;
    }
    .rate:not(:checked) > input {
        position: absolute;
        top: -9999px;
    }
    .rate:not(:checked) > label {
        float: right;
        width: 1em;
        overflow: hidden;
        white-space: nowrap;
        cursor: pointer;
        font-size: 30px;
        color: #ccc;
    }
    .rate:not(:checked) > label:before {
        content: '★ ';
    }
    .rate > input:checked ~ label {
        color: #ffc700;    
    }
    .rate:not(:checked) > label:hover,
    .rate:not(:checked) > label:hover ~ label {
        color: #deb217;  
    }
</style>