let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

// Objeto principal de la cita
const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}

// Variable para guardar las citas ocupadas del día seleccionado
let citasDelDia = [];

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); // Muestra la sección actual (1, 2 o 3)
    tabs(); // Cambia de sección al dar click en los tabs
    botonesPaginador(); // Agrega funcionalidad a botones Anterior/Siguiente
    paginaSiguiente(); 
    paginaAnterior();

    consultarAPI(); // Consulta la API de servicios (Backend)

    idCliente(); // Busca el ID del cliente en el HTML
    nombreCliente(); // Busca el nombre del cliente en el HTML
    seleccionarFecha(); // Validación de fecha y fines de semana
    seleccionarHora(); // Validación de hora y colisiones (15 mins)

    mostrarResumen(); // Muestra el resumen en el paso 3
}

function mostrarSeccion() {
    // 1. Ocultar la sección que tenga la clase de mostrar
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // 2. Seleccionar la sección con el paso actual y mostrarla
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // 3. Resaltar el Tab actual
    const tabAnterior = document.querySelector('.actual');
    if(tabAnterior) {
        tabAnterior.classList.remove('actual');
    }
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
        mostrarResumen(); // Cargar el resumen al llegar al final
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }
    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');
    paginaAnterior.addEventListener('click', function() {
        if(paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');
    paginaSiguiente.addEventListener('click', function() {
        if(paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {
    try {
        // Asegúrate que esta URL sea correcta en tu proyecto
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

    // Identificar el elemento al que se le da click
    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    // Comprobar si un servicio ya fue agregado
    if( servicios.some( agregado => agregado.id === id ) ) {
        // Eliminarlo
        cita.servicios = servicios.filter( agregado => agregado.id !== id );
        divServicio.classList.remove('seleccionado');
    } else {
        // Agregarlo
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }
}

function idCliente() {
    cita.id = document.querySelector('#id').value;
}

function nombreCliente() {
    cita.nombre = document.querySelector('#nombre').value;
}

// --- VALIDACIÓN DE FECHA ---
function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    
    inputFecha.addEventListener('input', function(e) {
        const dia = new Date(e.target.value).getUTCDay();

        // VALIDACIÓN 1: Fines de Semana (0=Domingo, 6=Sábado)
        if( [6, 0].includes(dia) ) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos', 'error', '.formulario');
        } else {
            // Si la fecha es válida, la guardamos
            cita.fecha = e.target.value;
            
            // Y consultamos las citas ocupadas para ese día (Para validar horas después)
            buscarCitasPorFecha(cita.fecha);
        }
    });
}

// --- CONSULTA DE CITAS OCUPADAS (Microservicio) ---
async function buscarCitasPorFecha(fecha) {
    try {
        // Asegúrate de que esta ruta exista en tu index.php
        // Puede ser /api/citas, /api/citas/programadas o /api/ms/citas
        const url = `/api/ms/citas?fecha=${fecha}`; 
        
        const respuesta = await fetch(url);
        const resultado = await respuesta.json();

        // Guardamos las citas encontradas en la variable global
        // Ajusta esto si tu API devuelve {agenda: [...]} o solo [...]
        citasDelDia = resultado.agenda || resultado; 

    } catch (error) {
        console.log('Error al buscar citas:', error);
    }
}

// --- VALIDACIÓN DE HORA ---
function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    
    inputHora.addEventListener('input', function(e) {
        const horaUsuario = e.target.value;
        const hora = horaUsuario.split(":")[0];

        // VALIDACIÓN 2: Horario Comercial (9am a 8pm)
        if(hora < 9 || hora > 20) {
            e.target.value = '';
            mostrarAlerta('Hora no válida. Abrimos de 9:00 a 20:00', 'error', '.formulario');
            return;
        }

        // VALIDACIÓN 3: Colisión de 15 minutos
        const choca = citasDelDia.some(citaBD => {
            const horaCita = citaBD.hora.split(":"); 
            const minCita = (parseInt(horaCita[0]) * 60) + parseInt(horaCita[1]);

            const horaInput = horaUsuario.split(":");
            const minInput = (parseInt(horaInput[0]) * 60) + parseInt(horaInput[1]);

            const diferencia = Math.abs(minCita - minInput);

            // Si hay menos de 15 minutos de diferencia
            return diferencia < 15;
        });

        if(choca) {
            e.target.value = '';
            mostrarAlerta('Horario ocupado. Debe haber 15 mins entre citas.', 'error', '.formulario');
        } else {
            // Si todo está bien, guardamos la hora
            cita.hora = e.target.value;
        }
    });
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if(alertaPrevia) {
        alertaPrevia.remove();
    }

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    if(desaparece) {
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');

    // Limpiar el contenido anterior
    while(resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    if(Object.values(cita).includes('') || cita.servicios.length === 0 ) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora', 'error', '.contenido-resumen', false);
        return;
    }

    // Formatear el div de resumen
    const { nombre, fecha, hora, servicios } = cita;

    // Header Servicios
    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    // Iterar y mostrar los servicios
    servicios.forEach(servicio => {
        const { id, precio, nombre } = servicio;
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

    // Header Cita
    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Cliente:</span> ${nombre}`;

    // Formatear la fecha en español
    const fechaObj = new Date(fecha);
    const mes = fechaObj.getMonth();
    const dia = fechaObj.getDate() + 2; // Ajuste por desfase de zona horaria JS
    const year = fechaObj.getFullYear();
    const fechaUTC = new Date(Date.UTC(year, mes, dia));
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
    const fechaFormateada = fechaUTC.toLocaleDateString('es-MX', opciones);

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

    // Botón para Crear Cita
    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(botonReservar);
}

// --- GUARDAR CITA EN EL SERVIDOR ---
async function reservarCita() {
    
    const { nombre, fecha, hora, servicios, id } = cita;
    const idServicios = servicios.map( servicio => servicio.id );

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
        // Asegúrate de tener esta ruta POST en tu Router
        const url = '/api/citas'; 
        
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if(resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: 'Tu cita fue creada correctamente',
                button: 'OK'
            }).then( () => {
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            })
        } else {
            // Aquí mostramos el error específico que manda el Backend
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.error || 'Hubo un error al guardar la cita'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error de conexión'
        });
    }
}