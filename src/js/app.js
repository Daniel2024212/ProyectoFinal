/***********************
 * VARIABLES GLOBALES *
 **********************/
let paso = 1;
const pasoInicial = 1;
const pasoFinal   = 4;   // ahora son 4 pasos

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: [],
    pago: {}      // aquí guardamos el pago
};

/***********************
 * INICIO DE LA APP   *
 **********************/
document.addEventListener('DOMContentLoaded', iniciarApp);

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

    manejarPago();      // <- NUEVO paso de pago
    mostrarResumen();
}

/***********************
 * NAVEGACIÓN WIZARD  *
 **********************/
function mostrarSeccion() {
    const seccionAnterior = document.querySelector('.mostrar');
    if (seccionAnterior) seccionAnterior.classList.remove('mostrar');

    document.querySelector(`#paso-${paso}`).classList.add('mostrar');

    const tabAnterior = document.querySelector('.actual');
    if (tabAnterior) tabAnterior.classList.remove('actual');

    document.querySelector(`[data-paso="${paso}"]`).classList.add('actual');
}

function tabs() {
    document.querySelectorAll('.tabs button').forEach(boton => {
        boton.addEventListener('click', e => {
            paso = parseInt(e.target.dataset.paso);
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const btnAnt = document.querySelector('#anterior');
    const btnSig = document.querySelector('#siguiente');

    if (paso === 1) {
        btnAnt.classList.add('ocultar');
        btnSig.classList.remove('ocultar');
    } else if (paso === pasoFinal) {
        btnAnt.classList.remove('ocultar');
        btnSig.classList.add('ocultar');
        mostrarResumen();
    } else {
        btnAnt.classList.remove('ocultar');
        btnSig.classList.remove('ocultar');
    }
    mostrarSeccion();
}

function paginaAnterior() {
    document.querySelector('#anterior').addEventListener('click', () => {
        paso = paso <= pasoInicial ? pasoInicial : paso - 1;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    document.querySelector('#siguiente').addEventListener('click', () => {
        paso = paso >= pasoFinal ? pasoFinal : paso + 1;
        botonesPaginador();
    });
}

/***********************
 * SERVICIOS (API)    *
 **********************/
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
    const divServicio = document.querySelector(`[data-id-servicio='${id}']`);

    if (servicios.some(agregado => agregado.id === id)) {
        cita.servicios = servicios.filter(s => s.id !== id);
        divServicio.classList.remove('seleccionado');
    } else {
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add('seleccionado');
    }
}

/***********************
 * DATOS DEL CLIENTE  *
 **********************/
function idCliente() {
    cita.id = document.querySelector('#id').value;
}
function nombreCliente() {
    cita.nombre = document.querySelector('#nombre').value;
}

/***********************
 * FECHA Y HORA       *
 **********************/
function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    inputFecha.addEventListener('keydown', e => e.preventDefault());

    inputFecha.addEventListener('input', e => {
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
    document.querySelector('#hora').addEventListener('input', e => {
        const horaCita = e.target.value;
        const hora = parseInt(horaCita.split(':')[0]);
        if (hora < 10 || hora > 18) {
            e.target.value = '';
            mostrarAlerta('Hora no válida', 'error', '.formulario');
        } else {
            cita.hora = horaCita;
        }
    });
}

/***********************
 * PAGO – PASO 3
 **********************/
function manejarPago() {
    const btn = document.getElementById('btn-pagar');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        if (cita.servicios.length === 0) {
            mostrarAlerta('Selecciona al menos un servicio antes de pagar', 'error', '#paso-2', false);
            return;
        }

        // calcular total y método
        const total = cita.servicios.reduce((acc, s) => acc + Number(s.precio), 0);
        const metodo = document.getElementById('pago-metodo').value;

        try {
            const res = await fetch('/api/pagos/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cita_id: 0,
                    usuario_id: cita.id,
                    monto: total,
                    metodo: metodo
                })
            });
            const json = await res.json();

            const salida = document.getElementById('pago-resultado');
            salida.textContent = json.success
                ? `Pago registrado. Ref: ${json.referencia || json.pago_id}`
                : 'Error en el pago';

            if (json.success) {
                // almacenar toda la info en servicio.precio
                servicio.precio = {
                    metodo,
                    monto: total,
                    referencia: json.referencia || json.pago_id,
                    estado: 'pagado',
                    fecha: new Date().toISOString()
                };

                paso = 3;
                botonesPaginador();
            }
        } catch (err) {
            mostrarAlerta('Error de conexión al pagar', 'error', '#paso-3', false);
        }
    });
}

/***********************
 * ALERTAS            *
 **********************/
function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if (alertaPrevia) alertaPrevia.remove();

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta', tipo);

    document.querySelector(elemento).appendChild(alerta);

    if (desaparece) {
        setTimeout(() => alerta.remove(), 3000);
    }
}

/***********************
 * RESUMEN – PASO 4   *
 **********************/
function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    while (resumen.firstChild) resumen.removeChild(resumen.firstChild);

    if (
        Object.values(cita).includes('') ||
        cita.servicios.length === 0 ||
        !cita.pago.referencia
    ) {
        mostrarAlerta('Faltan datos de servicio, pago, fecha u hora', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios, pago } = cita;

    const hServicios = document.createElement('H3');
    hServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(hServicios);

    servicios.forEach(s => {
        const cont = document.createElement('DIV');
        cont.classList.add('contenedor-servicio');

        const pNom = document.createElement('P');
        pNom.textContent = s.nombre;

        const pPrecio = document.createElement('P');
        pPrecio.innerHTML = `<span>Precio:</span> $${s.precio}`;

        cont.appendChild(pNom);
        cont.appendChild(pPrecio);
        resumen.appendChild(cont);
    });

    const hCita = document.createElement('H3');
    hCita.textContent = 'Resumen de Cita';
    resumen.appendChild(hCita);

    const pNombre = document.createElement('P');
    pNombre.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const fechaStr = fechaObj.toLocaleDateString('es-MX',
        { weekday:'long', year:'numeric', month:'long', day:'numeric' });

    const pFecha = document.createElement('P');
    pFecha.innerHTML = `<span>Fecha:</span> ${fechaStr}`;

    const pHora = document.createElement('P');
    pHora.innerHTML = `<span>Hora:</span> ${hora} hrs`;

    const hPago = document.createElement('H3');
    hPago.textContent = 'Pago';
    const pPago = document.createElement('P');
    pPago.innerHTML = `<span>Método:</span> ${pago.metodo} — <span>Monto:</span> $${pago.monto}`;

    const btn = document.createElement('BUTTON');
    btn.classList.add('boton');
    btn.textContent = 'Confirmar Cita';
    btn.onclick = reservarCita;

    resumen.append(pNombre, pFecha, pHora, hPago, pPago, btn);
}

/***********************
 * GUARDAR CITA       *
 **********************/
async function reservarCita() {
    const { nombre, fecha, hora, servicios, id } = cita;
    const idServicios = servicios.map(s => s.id);

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
        const url = `${location.origin}/api/citas`;
        const resp = await fetch(url, { method: 'POST', body: datos });
        const res = await resp.json();

        if (res.resultado) {
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
