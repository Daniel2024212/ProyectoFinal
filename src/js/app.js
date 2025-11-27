let paso = 1;
const pasoInicial = 1;
const pasoFinal = 5;

const cita = {
    id: '', // Se llena al iniciar sesión
    nombre: '',
    fecha: '',
    hora: '',
    servicios: [],
    citaIdGenerada: null // Guardaremos el ID que devuelva el Microservicio de Citas
};

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); // Muestra el paso actual
    tabs(); // Listeners para los tabs
    botonesPaginador(); // Listeners para Anterior/Siguiente
    paginaSiguiente();
    paginaAnterior();

    consultarAPI(); // Carga servicios del backend

    idCliente();
    nombreCliente();
    seleccionarFecha();
    seleccionarHora();

    // Listeners para los botones de acción final de cada microservicio
    listenerPagar();
    listenerValorar();
}

function mostrarSeccion() {
    // Ocultar sección previa
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) seccionAnterior.classList.remove('mostrar');

    // Mostrar sección actual por ID
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // Resaltar Tab actual
    const tabAnterior = document.querySelector('.actual');
    if(tabAnterior) tabAnterior.classList.remove('actual');

    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');
    botones.forEach(boton => {
        boton.addEventListener('click', function(e) {
            // Evitar saltar pasos sin completar la lógica (ej. no ir a pago sin cita)
            const pasoDeseado = parseInt(e.target.dataset.paso);
            
            // Validación simple de flujo
            if(pasoDeseado === 4 && !cita.citaIdGenerada) {
                Swal.fire('Atención', 'Debes confirmar la cita en el Resumen antes de pagar', 'warning');
                return;
            }
            if(pasoDeseado === 5 && !cita.citaIdGenerada) {
                Swal.fire('Atención', 'Debes completar el pago primero', 'warning');
                return;
            }

            paso = pasoDeseado;
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
    } else if (paso === pasoFinal) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }

    if(paso === 3) {
        mostrarResumen(); // Cargar resumen al llegar al paso 3
    }
    
    // Calcular total si estamos en paso de pago
    if(paso === 4) {
        calcularTotalPago();
    }
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');
    paginaAnterior.addEventListener('click', function() {
        if(paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
        mostrarSeccion();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');
    paginaSiguiente.addEventListener('click', function() {
        if(paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
        mostrarSeccion();
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
    servicios.forEach(servicio => {
        const {id, nombre, precio} = servicio;

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
    const {id} = servicio;
    const {servicios} = cita;

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
        const horaCita = e.target.value;
        const hora = horaCita.split(":")[0];
        if(hora < 10 || hora > 18) {
            e.target.value = '';
            mostrarAlerta('Hora no válida', 'error', '.formulario');
        } else {
            cita.hora = horaCita;
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
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');

    // Limpiar contenido previo
    while(resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    if(Object.values(cita).includes('') || cita.servicios.length === 0 ) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora', 'error', '.contenido-resumen', false);
        return;
    }

    // Formatear el div de resumen
    const {nombre, fecha, hora, servicios} = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    servicios.forEach(servicio => {
        const {id, precio, nombre} = servicio;
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

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

    // Formatear la fecha en español
    const fechaObj = new Date(fecha);
    const mes = fechaObj.getMonth();
    const dia = fechaObj.getDate() + 2;
    const year = fechaObj.getFullYear();
    const fechaUTC = new Date( Date.UTC(year, mes, dia));
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
    const fechaFormateada = fechaUTC.toLocaleDateString('es-MX', opciones);

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

    // Botón para Crear Cita
    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Confirmar Cita';
    botonReservar.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(botonReservar);
}

async function reservarCita() {
    // 1. LLAMADA AL MICROSERVICIO DE CITAS
    const {nombre, fecha, hora, servicios, id} = cita;
    const idServicios = servicios.map( servicio => servicio.id );

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
        // Petición hacia la API Gateway (Controller)
        const url = '/api/citas';
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if(resultado.resultado) {
            // Guardamos el ID que nos devolvió el servicio para usarlo en Pagos y Valoraciones
            cita.citaIdGenerada = resultado.id; 
            
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: 'Tu cita fue creada correctamente. Ahora procede al pago.',
                button: 'OK'
            }).then( () => {
                // Avanzamos automáticamente al paso de Pago
                paso = 4;
                mostrarSeccion();
                botonesPaginador();
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al guardar la cita'
        });
    }
}

function calcularTotalPago() {
    // Sumar precios de servicios seleccionados
    const total = cita.servicios.reduce( (total, servicio) => total + parseFloat(servicio.precio), 0 );
    const totalParrafo = document.querySelector('#pago-total');
    totalParrafo.textContent = `$${total.toFixed(2)}`;
}

function listenerPagar() {
    const btnPagar = document.querySelector('#btn-pagar');
    btnPagar.addEventListener('click', async () => {
        
        // Validaciones previas
        if(!cita.citaIdGenerada) {
            Swal.fire('Error', 'No hay una cita creada para pagar', 'error');
            return;
        }

        const metodoPago = document.querySelector('#metodo-pago').value;
        if(!metodoPago) {
            Swal.fire('Error', 'Selecciona un método de pago', 'error');
            return;
        }

        const total = cita.servicios.reduce( (acc, curr) => acc + parseFloat(curr.precio), 0 );

        // 2. LLAMADA AL MICROSERVICIO DE PAGOS
        const datosPago = {
            usuario_id: cita.id,
            cita_id: cita.citaIdGenerada,
            monto: total,
            metodo: metodoPago
        };

        try {
            const url = '/api/pagos/crear';
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosPago)
            });
            const resultado = await respuesta.json();

            if(resultado.success) {
                Swal.fire('Pago Exitoso', `Referencia: ${resultado.referencia}`, 'success')
                    .then(() => {
                        // Avanzamos a valoración
                        paso = 5; 
                        mostrarSeccion();
                        botonesPaginador();
                    });
            } else {
                Swal.fire('Error en Pago', resultado.error || 'No se pudo procesar', 'error');
            }

        } catch (error) {
            console.log(error);
        }
    });
}

function listenerValorar() {
    const btnValorar = document.querySelector('#btn-valorar');
    btnValorar.addEventListener('click', async () => {
        
        if(!cita.citaIdGenerada) return;

        const estrellas = document.querySelector('#estrellas').value;
        const comentario = document.querySelector('#comentario').value;

        // 3. LLAMADA AL MICROSERVICIO DE FEEDBACK
        const datosValoracion = {
            usuario_id: cita.id,
            cita_id: cita.citaIdGenerada,
            estrellas: estrellas,
            comentario: comentario
        };

        try {
            const url = '/api/valoraciones/crear';
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosValoracion)
            });
            const resultado = await respuesta.json();

            if(resultado.success) {
                Swal.fire('Gracias', 'Tu valoración ha sido guardada', 'success')
                    .then(() => {
                        window.location.reload(); // Reiniciar app para nueva cita
                    });
            } else {
                Swal.fire('Error', resultado.error || 'No se pudo guardar la valoración', 'error');
            }

        } catch (error) {
            console.log(error);
        }
    });
}