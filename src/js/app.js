let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); 
    tabs(); 
    botonesPaginador(); 
    paginaSiguiente(); 
    paginaAnterior();

    consultarAPI(); 

    idCliente(); 
    nombreCliente(); // <--- ESTA ES LA FUNCIÓN CLAVE
    seleccionarFecha(); 
    seleccionarHora(); 

    mostrarResumen(); 
}

function mostrarSeccion() {
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) seccionAnterior.classList.remove('mostrar');

    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    const tabAnterior = document.querySelector('.actual');
    if(tabAnterior) tabAnterior.classList.remove('actual');

    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach( boton => {
        boton.addEventListener('click', function(e) {
            paso = parseInt( e.target.dataset.paso );
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguiente = document.querySelector('#siguiente');

    if(paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    } else if (paso === 3) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
        mostrarResumen();
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }
    mostrarSeccion();
}

function paginaAnterior() {
    document.querySelector('#anterior').addEventListener('click', function() {
        if(paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    document.querySelector('#siguiente').addEventListener('click', function() {
        if(paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {
    try {
        const url = '/api/servicios';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
    } catch (error) {
        console.log(error);
    }
}

function mostrarServicios(servicios) {
    servicios.forEach( servicio => {
        const { id, nombre, precio } = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.classList.add('nombre-servicio');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.classList.add('precio-servicio');
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function() {
            seleccionarServicio(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);
        document.querySelector('#servicios').appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const { servicios } = cita;
    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    if( servicios.some( agregado => agregado.id === id ) ) {
        cita.servicios = servicios.filter( agregado => agregado.id !== id );
        divServicio.classList.remove('seleccionado');
    } else {
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }
}

function idCliente() {
    cita.id = document.querySelector('#id').value;
}

// --- FUNCIÓN CORREGIDA PARA DETECTAR EL NOMBRE ---
function nombreCliente() {
    const nombreInput = document.querySelector('#nombre');
    
    // 1. Guardar valor inicial (por si no lo editan)
    cita.nombre = nombreInput.value;

    // 2. Escuchar CADA letra que escribes
    nombreInput.addEventListener('input', function(e) {
        const nombreTexto = e.target.value.trim();
        
        // Debugging: Mira la consola (F12) si sale este mensaje al escribir
        console.log("Nuevo nombre detectado:", nombreTexto);

        if(nombreTexto === '' || nombreTexto.length < 3) {
            mostrarAlerta('Nombre no válido', 'error', '.formulario');
        } else {
            const alerta = document.querySelector('.alerta');
            if(alerta) alerta.remove();
            
            // ACTUALIZAMOS EL OBJETO GLOBAL
            cita.nombre = nombreTexto;
        }
    });
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    inputFecha.addEventListener('input', function(e) {
        const dia = new Date(e.target.value).getUTCDay();
        if( [6, 0].includes(dia) ) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos', 'error', '.formulario');
        } else {
            cita.fecha = e.target.value;
        }
    });
}

function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    inputHora.addEventListener('input', function(e) {
        const horaUsuario = e.target.value;
        const hora = horaUsuario.split(":")[0];
        if(hora < 9 || hora > 20) {
            e.target.value = '';
            mostrarAlerta('Hora no válida', 'error', '.formulario');
        } else {
            cita.hora = e.target.value;
        }
    });
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if(alertaPrevia) alertaPrevia.remove();

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    if(desaparece) {
        setTimeout(() => { alerta.remove(); }, 3000);
    }
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    while(resumen.firstChild) resumen.removeChild(resumen.firstChild);

    if(Object.values(cita).includes('') || cita.servicios.length === 0 ) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    servicios.forEach(servicio => {
        const { precio, nombre } = servicio;
        const contenedorServicio = document.createElement('DIV');
        contenedorServicio.classList.add('contenedor-servicio');

        const textoServicio = document.createElement('P');
        textoServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.innerHTML = `<span>Precio:</span> $${precio}`;

        contenedorServicio.appendChild(textoServicio);
        contenedorServicio.appendChild(precioServicio);
        resumen.appendChild(contenedorServicio);
    });

    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    // AQUÍ MOSTRAMOS EL NOMBRE (Debería ser el editado)
    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Cliente:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const mes = fechaObj.getMonth();
    const dia = fechaObj.getDate() + 2;
    const year = fechaObj.getFullYear();
    const fechaUTC = new Date(Date.UTC(year, mes, dia));
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
    const fechaFormateada = fechaUTC.toLocaleDateString('es-MX', opciones);

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(botonReservar);
}

async function reservarCita() {
    const { nombre, fecha, hora, servicios, id } = cita;
    
    // Validación extra
    if(!nombre || nombre.length < 3) {
         Swal.fire({icon: 'error', title: 'Error', text: 'El nombre es obligatorio'});
         return;
    }

    const idServicios = servicios.map( servicio => servicio.id );
    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);
    
    // ENVIAMOS EL NOMBRE EDITADO AL SERVIDOR
    datos.append('cliente', nombre); 

    try {
        const url = '/api/citas'; 
        const respuesta = await fetch(url, { method: 'POST', body: datos });
        const texto = await respuesta.text(); 
        
        let resultado;
        try { resultado = JSON.parse(texto); } 
        catch (e) {
            Swal.fire({icon: 'error', title: 'Error PHP', text: 'Revisa consola'});
            console.error(texto);
            return;
        }

        if(resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: `Cita registrada para: ${nombre}`, // CONFIRMACIÓN VISUAL
                button: 'OK'
            }).then( () => {
                setTimeout(() => { window.location.reload(); }, 1500);
            })
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: resultado.error || 'No se pudo guardar'
            });
        }
    } catch (error) {
        Swal.fire({icon: 'error', title: 'Error', text: 'Error de conexión'});
    }
}