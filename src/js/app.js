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

document.addEventListener('DOMContentLoaded', function () {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); // Muestra y oculta las secciones.
    tabs(); // Cambia la sección cuando se presionen los tabs.
    botonesPaginador(); // Agrega o quita los botones del paginador.
    paginaSiguiente();
    paginaAnterior();

    consultarAPI(); // Consulta la API en el backend de PHP.

    idCliente(); // Añade el id al ojeto de cliente.
    nombreCliente(); // Añade el nombre del cliente al objeto de cita.
    seleccionarFecha(); // Añade la fecha de la cita en el ojeto de cita.
    seleccionarHora(); // Añada la hora de la cita en el objeto cita.

    mostrarResumen(); // Muestra el resumen de la cita.
}

function mostrarSeccion() {
    // Ocultar la sección que tenga la clase de mostrar.
    const seccionAnterior = document.querySelector('.mostrar');
    if (seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // Seleccionar la sección con el paso:
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // Ocultar el tab que tenga la clase de actual.
    const tabAnteiror = document.querySelector('.actual');
    if (tabAnteiror) {
        tabAnteiror.classList.remove('actual');
    }

    // Resalta el tab actual:
    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');

    botones.forEach(boton => {
        boton.addEventListener('click', function (e) {
            paso = parseInt(e.target.dataset.paso);

            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguite = document.querySelector('#siguiente');

    if (paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguite.classList.remove('ocultar'); // Visible
    } else if (paso === 3) {
        paginaAnterior.classList.remove('ocultar'); // Visible
        paginaSiguite.classList.add('ocultar');
        mostrarResumen();
    } else {
        paginaAnterior.classList.remove('ocultar'); // Visible
        paginaSiguite.classList.remove('ocultar'); // Visible
    }

    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.querySelector('#anterior');

    paginaAnterior.addEventListener('click', function () {
        paso <= pasoInicial ? paso = 1 : paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.querySelector('#siguiente');

    paginaSiguiente.addEventListener('click', function () {
        paso >= pasoFinal ? paso = 3 : paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {
    try {

        const url = `${location.origin}/api/servicios`;
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);

    } catch (error) {
        console.log(error);
    }
}

function mostrarServicios(servicios) {
    servicios.forEach(servicio => {
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
        servicioDiv.onclick = function () {
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
    // Identificar el elemento al que se le da click:
    const divServicio = document.querySelector(`[data-id-servicio='${id}'`);

    // Comprobar si un servicio ya fue seleccionado:
    if (servicios.some(agregado => agregado.id === id)) {
        // Eliminarlo:
        cita.servicios = servicios.filter(agregado => agregado.id != id);
        divServicio.classList.remove('seleccionado');
    } else {
        // Agregarlo:
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }
}

function idCliente() {
    cita.id = document.querySelector('#id').value;
}

function nombreCliente() {
    const nombre = document.querySelector('#nombre').value;
    cita.nombre = nombre;
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');

    // Bloquear escritura manual
    inputFecha.addEventListener('keydown', function (e) {
        e.preventDefault();
    });


    inputFecha.addEventListener('input', function (e) {
        const dia = new Date(e.target.value).getUTCDay();
        if ([6, 0].includes(dia)) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos', 'error', '.formulario');
        } else {
            cita.fecha = e.target.value;
        }
    });
}

function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    inputHora.addEventListener('input', function (e) {
        const horaCita = e.target.value;
        const hora = horaCita.split(':')[0];

        if (hora < 10 || hora > 18) {
            e.target.value = '';
            mostrarAlerta('Hora no válida', 'error', '.formulario');
        } else {
            cita.hora = horaCita;
        }
    });
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');

    // Previene que se genere más de una alerta:
    if (alertaPrevia) {
        alertaPrevia.remove();
    }

    // Scripting para crear una alerta:
    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    // Eliminar alerta:
    if (desaparece) {
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}

function mostrarResumen() {
  const resumen = document.querySelector('.contenido-resumen');

  // Limpiar contenido previo
  while (resumen.firstChild) {
    resumen.removeChild(resumen.firstChild);
  }

  // Validación de datos
  if (Object.values(cita).includes('') || cita.servicios.length === 0) {
    mostrarAlerta('Faltan datos de servicio, fecha u hora', 'error', '.contenido-resumen', false);
    return;
  }

  const { fecha, hora, servicios } = cita;
  const nombre1 = document.getElementById('nombre')?.value.trim() || 'No disponible';

  // Sección de servicios
  const headingServicios = document.createElement('h3');
  headingServicios.textContent = 'Resumen de Servicios';
  resumen.appendChild(headingServicios);

  servicios.forEach(servicio => {
    const { precio, nombre } = servicio;

    const contenedorServicio = document.createElement('div');
    contenedorServicio.classList.add('contenedor-servicio');

    const textServicio = document.createElement('p');
    textServicio.textContent = nombre;

    const precioServicio = document.createElement('p');
    precioServicio.innerHTML = `<span>Precio:</span> $${precio}`;

    contenedorServicio.appendChild(textServicio);
    contenedorServicio.appendChild(precioServicio);
    resumen.appendChild(contenedorServicio);
  });

  // Sección de cita
  const headingCita = document.createElement('h3');
  headingCita.textContent = 'Resumen de Cita';
  resumen.appendChild(headingCita);

  const nombreCliente = document.createElement('p');
  nombreCliente.innerHTML = '<span>Nombre:</span> ' + nombre1;

  const fechaObj = new Date(fecha);
  const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const fechaFormateada = fechaObj.toLocaleDateString('es-MX', opciones);

  const fechaCita = document.createElement('p');
  fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

  const horaCita = document.createElement('p');
  horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

  const botonReservar = document.createElement('button');
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

    const idServicios = servicios.map(servicio => servicio.id);

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    // console.log([...datos]);
    // return;
    try {
        // Petición hacia la API:
        const url = `${location.origin}/api/citas`;

        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if (resultado.resultado) {
            Swal.fire({
                icon: 'success',
                title: 'Cita Creada',
                text: 'Tu cita fue creada correctamente',
                button: 'OK'
            }).then(() => setTimeout(() => window.location.reload(), 3000));
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un error al guardar la cita',
            button: 'OK'
        });
    }

}