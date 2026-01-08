<h1 class="nombre-pagina">Crear Administrador</h1>
<p class="descripcion-pagina">Llena el formulario para crear una cuenta ADMIN</p>

<?php 
    include_once __DIR__ . "/../templates/alertas.php";
?>

<form class="formulario" method="POST" action="/crear-admin">
    <div class="campo">
        <label for="nombre">Nombre</label>
        <input 
            type="text" 
            id="nombre" 
            name="nombre" 
            placeholder="Tu Nombre"
            value="<?php echo s($usuario->nombre); ?>"
        />
    </div>

    <div class="campo">
        <label for="apellido">Apellido</label>
        <input 
            type="text" 
            id="apellido" 
            name="apellido" 
            placeholder="Tu Apellido"
            value="<?php echo s($usuario->apellido); ?>"
        />
    </div>

    <div class="campo">
        <label for="telefono">Teléfono</label>
        <input 
            type="tel" 
            id="telefono" 
            name="telefono" 
            placeholder="Tu Teléfono"
            value="<?php echo s($usuario->telefono); ?>"
        />
    </div>

    <div class="campo">
        <label for="email">E-mail</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="Tu E-mail"
            value="<?php echo s($usuario->email); ?>"
        />
    </div>

    <div class="campo">
        <label for="password">Password</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Tu Password"
        />
    </div>

    <input type="submit" value="Crear Admin" class="boton">
</form>

<div class="acciones" style="margin-top: 3rem; text-align: center;">
    <a href="/login" class="boton-verde">Ir a Iniciar Sesión</a>
</div>

<style>
    .boton-verde {
        background-color: #2ecc71; /* Color Verde Éxito */
        color: white;
        padding: 1.5rem 4rem;
        text-decoration: none;
        font-weight: 700;
        border-radius: .5rem;
        text-transform: uppercase;
        display: inline-block;
        font-size: 1.4rem; 
        transition: background-color .3s ease;
        border: 1px solid #27ae60;
    }
    .boton-verde:hover {
        background-color: #27ae60; /* Verde más oscuro al pasar mouse */
        cursor: pointer;
    }
</style>