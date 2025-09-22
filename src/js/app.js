// =====================
// Variables globales
// =====================
let paso = 1;
const pasoInicial = 1;
const pasoFinal   = 4; // 1=Servicios, 2=Pago, 3=Datos, 4=Resumen
const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
};

// =====================
// Inicio
// =====================
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

    mostrarResumen();
}

// =====================
// Navegación de pasos
// =====================
function mostrarSeccion() {
    const seccionAnterior = document.querySelector('.mostrar');
    if (seccionAnterior) seccionAnterior.classList.remove('mostrar');

    const seccion = document.querySelector(`#paso-${paso}`);
    if (seccion) seccion.classList.add('mostrar');

    const tabAnterior = document.querySelector('.actual');
    if (tabAnterior) tabAnterior.classList.remove('actual');

    const tab = document.querySelector(`[data-paso="${paso}"]`);
    if (tab) tab.classList.add('actual');
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
    const anterior = document.querySelector('#anterior');
    const siguiente = document.querySelector('#siguiente');

    if (paso === 1) {
        anterior.classList.add('ocultar');
        siguiente.classList.remove('ocultar');
    } else if (paso === pasoFinal) {
        anterior.classList.remove('ocultar');
        siguiente.classList.add('ocultar');
        mostrarResumen();
    } else {
        anterior.classList.remove('ocultar');
        siguiente.classList.remove('ocultar');
    }

    mostrarSeccion();
}

function paginaAnterior() {
    document.querySelector('#anterior').addEventListener('click', () => {
        paso <= pasoInicial ? paso = 1 : paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    document.querySelector('#siguiente').addEventListener('click', () => {
        paso >= pasoFinal ? paso = pasoFinal : paso++;
        botonesPaginador();
    });
}

// =====================
// API de Servicios
// =====================
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
        servicioDiv.onclick = () => seleccionarServicio(servicio);

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        document.querySelector('#servicios').appendChild(servicioDiv);
    });
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

        // Clic para sumar
        servicioDiv.onclick = () => seleccionarServicio(servicio);

        // Botón para restar
        const btnMenos = document.createElement('button');
        btnMenos.textContent = '–';
        btnMenos.type = 'button';
        btnMenos.style.cssText = 'margin-left:auto;margin-right:4px;';
        btnMenos.addEventListener('click', e => {
            e.stopPropagation(); // Evita que sume al hacer clic en el div
            disminuirServicio(id);
        });

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);
        servicioDiv.appendChild(btnMenos);

        document.querySelector('#servicios').appendChild(servicioDiv);
    });
}


// =====================
// Datos del cliente
// =====================
function idCliente() {
    const inputId = document.querySelector('#id');
    if (inputId) cita.id = inputId.value;
}

function nombreCliente() {
    const inputNombre = document.querySelector('#nombre_cliente');
    if (inputNombre) {
        inputNombre.addEventListener('input', e => {
            cita.nombre = e.target.value;
        });
    }
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    if (!inputFecha) return;

    inputFecha.addEventListener('keydown', e => e.preventDefault());
    inputFecha.addEventListener('input', e => {
        const dia = new Date(e.target.value).getUTCDay();
        if ([6,0].includes(dia)) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos','error','.formulario');
        } else {
            cita.fecha = e.target.value;
        }
    });
}

function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    if (!inputHora) return;

    inputHora.addEventListener('input', e => {
        const horaCita = e.target.value;
        const hora = horaCita.split(':')[0];
        if (hora < 10 || hora > 18) {
            e.target.value = '';
            mostrarAlerta('Hora no válida','error','.formulario');
        } else {
            cita.hora = horaCita;
        }
    });
}

// =====================
// Alertas
// =====================
function mostrarAlerta(mensaje, tipo, selector, desaparece = true) {
    const alertaPrevia = document.querySelector('.alerta');
    if (alertaPrevia) alertaPrevia.remove();

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta', tipo);

    const referencia = document.querySelector(selector);
    referencia.appendChild(alerta);

    if (desaparece) {
        setTimeout(() => alerta.remove(), 3000);
    }
}

// =====================
// Resumen final
// =====================
function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');
    if (!resumen) return;

    while (resumen.firstChild) resumen.removeChild(resumen.firstChild);

    if (cita.nombre === '' || cita.fecha === '' || cita.hora === '' || cita.servicios.length === 0) {
        mostrarAlerta('Faltan datos de servicio, fecha u hora', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    servicios.forEach(serv => {
        const { nombre, precio } = serv;
        const contenedor = document.createElement('DIV');
        contenedor.classList.add('contenedor-servicio');

        const txt = document.createElement('P');
        txt.textContent = nombre;

        const precioEl = document.createElement('P');
        precioEl.innerHTML = `<span>Precio:</span> $${precio}`;

        contenedor.appendChild(txt);
        contenedor.appendChild(precioEl);
        resumen.appendChild(contenedor);
    });

    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const fechaFormateada = fechaObj.toLocaleDateString('es-MX',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

    const fechaCita = document.createElement('P');
    fechaCita.innerHTML = `<span>Fecha:</span> ${fechaFormateada}`;

    const horaCita = document.createElement('P');
    horaCita.innerHTML = `<span>Hora:</span> ${hora} Hrs`;

    const boton = document.createElement('BUTTON');
    boton.classList.add('boton');
    boton.textContent = 'Reservar Cita';
    boton.onclick = reservarCita;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(fechaCita);
    resumen.appendChild(horaCita);
    resumen.appendChild(boton);
}

// =====================
// Reservar Cita
// =====================
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
            Swal.fire('Cita Creada', 'Tu cita fue creada correctamente', 'success')
                .then(() => window.location.reload());
        }
    } catch (error) {
        Swal.fire('Error', 'Hubo un error al guardar la cita', 'error');
    }
}

// =====================
// Pago en Efectivo
// =====================
document.addEventListener('DOMContentLoaded', () => {
    const btnPagar = document.getElementById('btn-pagar');
    btnPagar?.addEventListener('click', async () => {
        const monto = cita.servicios.reduce((acc, s) => acc + parseFloat(s.precio), 0);
        if (monto <= 0) {
            Swal.fire('Error','Debes seleccionar al menos un servicio','error');
            return;
        }

        try {
            const res = await fetch('/api/pagos/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    usuario_id: cita.id,
                    cita_id: cita.id,
                    monto: monto,
                    metodo: 'efectivo'
                })
            });
            const json = await res.json();
            if (json.success) {
                document.getElementById('pago-resultado').textContent =
                    `Pago en efectivo registrado. Folio: ${json.pago_id}`;
                paso = 3;
                botonesPaginador();
            } else {
                Swal.fire('Error', json.error || 'No se pudo registrar el pago', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error','Error de conexión al procesar el pago','error');
        }
    });
});
