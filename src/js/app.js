let paso = 1;
const pasoInicial = 1;
const pasoFinal = 5;   // ahora tenemos 5 pasos

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: [],
    pago: null        // para guardar info de pago
};

document.addEventListener('DOMContentLoaded', function () {
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
    nombreCliente();
    seleccionarFecha();
    seleccionarHora();
    inicializarPago();        // NUEVO: manejo de pago
    inicializarValoracion();  // NUEVO: manejo de valoración
    mostrarResumen();
}

function mostrarSeccion() {
    const seccionAnterior = document.querySelector('.mostrar');
    if (seccionAnterior) seccionAnterior.classList.remove('mostrar');

    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    if (seccion) seccion.classList.add('mostrar');

    const tabAnterior = document.querySelector('.actual');
    if (tabAnterior) tabAnterior.classList.remove('actual');

    const tab = document.querySelector(`[data-paso="${paso}"]`);
    if (tab) tab.classList.add('actual');
}

function tabs() {
    document.querySelectorAll('.tabs button').forEach(boton => {
        boton.addEventListener('click', function (e) {
            paso = parseInt(e.target.dataset.paso);
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const paginaAnterior = document.querySelector('#anterior');
    const paginaSiguiente = document.querySelector('#siguiente');

    if (paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    } else if (paso === 4) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar'); // permite ir a 5
        mostrarResumen();
    } else if (paso === 5) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');
        mostrarValoracion();
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    }
    mostrarSeccion();
}

function paginaAnterior() {
    document.querySelector('#anterior').addEventListener('click', function () {
        paso <= pasoInicial ? paso = 1 : paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    document.querySelector('#siguiente').addEventListener('click', function () {
        paso >= pasoFinal ? paso = pasoFinal : paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {
    try {
        const url = `${location.origin}/api/servicios`;
        const resultado = await fetch(url);
        mostrarServicios(await resultado.json());
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
        servicioDiv.onclick = () => seleccionarServicio(servicio);

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);
        document.querySelector('#servicios').appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const divServicio = document.querySelector(`[data-id-servicio='${id}'`);
    if (cita.servicios.some(s => s.id === id)) {
        cita.servicios = cita.servicios.filter(s => s.id !== id);
        divServicio.classList.remove('seleccionado');
    } else {
        cita.servicios.push(servicio);
        divServicio.classList.add('seleccionado');
    }
}

function idCliente() {
    const input = document.querySelector('#id');
    if (input) cita.id = input.value;
}

function nombreCliente() {
    const inputNombre = document.querySelector('#nombre_cliente');
    if (!inputNombre) return;
    inputNombre.addEventListener('input', e => {
        cita.nombre = e.target.value;
    });
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    if (!inputFecha) return;

    inputFecha.addEventListener('keydown', e => e.preventDefault());
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
    if (!inputHora) return;
    inputHora.addEventListener('input', function (e) {
        const hora = e.target.value.split(':')[0];
        if (hora < 10 || hora > 18) {
            e.target.value = '';
            mostrarAlerta('Hora no válida', 'error', '.formulario');
        } else {
            cita.hora = e.target.value;
        }
    });
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if (alertaPrevia) alertaPrevia.remove();

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta', tipo);

    document.querySelector(elemento).appendChild(alerta);
    if (desaparece) setTimeout(() => alerta.remove(), 3000);
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    while (resumen.firstChild) resumen.removeChild(resumen.firstChild);

    if (cita.nombre === '' || cita.fecha === '' || cita.hora === '' || cita.servicios.length === 0) {
        mostrarAlerta('Faltan datos para la cita', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    servicios.forEach(servicio => {
        const { precio, nombre } = servicio;
        const contenedor = document.createElement('DIV');
        contenedor.classList.add('contenedor-servicio');
        contenedor.innerHTML = `<p>${nombre}</p><p><span>Precio:</span> $${precio}</p>`;
        resumen.appendChild(contenedor);
    });

    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const fechaFormateada = fechaObj.toLocaleDateString('es-MX',
        { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Horas`;

    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Cita';
    botonReservar.onclick = reservarCita;

    resumen.append(nombreCliente, fechaCita, horaCita, botonReservar);
}

async function reservarCita() {
    const { nombre, fecha, hora, servicios, id } = cita;
    const idServicios = servicios.map(s => s.id);

    const datos = new FormData();
    datos.append('nombre_cliente', nombre);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
        const url = `${location.origin}/api/citas`;
        const respuesta = await fetch(url, { method: 'POST', body: datos });
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
        Swal.fire({ icon: 'error', title: 'Error', text: 'Hubo un error al guardar la cita' });
    }
}

/* ===================== NUEVO: PAGO ===================== */
function inicializarPago() {
    const btn = document.getElementById('btn-pagar');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        if (cita.servicios.length === 0) {
            mostrarAlerta('Selecciona al menos un servicio antes de pagar',
                          'error', '#paso-2', false);
            return;
        }

        const nombrePago = document.getElementById('pago-nombre').value.trim();
        if (!nombrePago) {
            mostrarAlerta('Ingresa tu nombre', 'error', '#paso-2', false);
            return;
        }

        const total  = cita.servicios.reduce((s, srv) => s + Number(s.precio), 0);
        const metodo = document.getElementById('pago-metodo').value;

        try {
            const res = await fetch('/api/pagos/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cita_id: 0,
                    usuario_id: cita.id,
                    nombre: nombrePago,
                    monto: total,
                    metodo: metodo
                })
            });

            const json = await res.json();
            document.getElementById('pago-resultado').textContent =
                json.success ? `Pago registrado. Ref: ${json.referencia}` : `Error: ${json.error}`;

            if (json.success) {
                cita.nombre = nombrePago;
                cita.pago = { metodo, monto: total, referencia: json.referencia };
                paso = 3;
                botonesPaginador();
            }
        } catch (err) {
            mostrarAlerta('Error de conexión al pagar', 'error', '#paso-2', false);
        }
    });
}

/* ===================== NUEVO: VALORACIÓN ===================== */
function inicializarValoracion() {
    // Se prepara la escucha, se mostrará en mostrarValoracion
    const btn = document.getElementById('btn-valoracion');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const estrellas  = document.getElementById('valoracion-estrellas').value;
        const comentario = document.getElementById('valoracion-comentario').value;

        const fechaHoraCita = new Date(`${cita.fecha}T${cita.hora}`);
        if (fechaHoraCita > new Date()) {
            mostrarAlerta('La cita aún no ha terminado', 'error', '#paso-5', false);
            return;
        }

        const res = await fetch('/api/valoraciones/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cita_id: cita.id,
                usuario_id: cita.id,
                estrellas: estrellas,
                comentario: comentario
            })
        });
        const json = await res.json();
        document.getElementById('valoracion-resultado').textContent =
            json.success ? '¡Gracias por tu valoración!' : `Error: ${json.error}`;
    });
}

function mostrarValoracion() {
    // Muestra la sección de valoración (paso 5) si ya pasó la cita
    const seccion = document.querySelector('#paso-5');
    if (!seccion) return;

    const fechaHoraCita = new Date(`${cita.fecha}T${cita.hora}`);
    if (fechaHoraCita <= new Date()) {
        seccion.classList.add('mostrar');
    } else {
        mostrarAlerta('La cita aún no ha terminado', 'error', '#paso-5', false);
    }
}
