// Pasos del wizard
let paso = 1;
const pasoInicial = 1,
      pasoFinal   = 4;

const cita = {
    id: "",
    nombre: "",
    fecha: "",
    hora: "",
    servicios: [],
    pago: {} // para guardar info del pago
};

// ---------- Inicio ----------
document.addEventListener("DOMContentLoaded", iniciarApp);

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
    manejarPago();
    mostrarResumen();
}

// ---------- Navegación ----------
function mostrarSeccion() {
    const anterior = document.querySelector(".mostrar");
    if (anterior) anterior.classList.remove("mostrar");

    const seccion = `#paso-${paso}`;
    document.querySelector(seccion).classList.add("mostrar");

    const tabAnterior = document.querySelector(".actual");
    if (tabAnterior) tabAnterior.classList.remove("actual");
    document.querySelector(`[data-paso="${paso}"]`).classList.add("actual");
}

function tabs() {
    document.querySelectorAll(".tabs button").forEach(btn => {
        btn.addEventListener("click", e => {
            paso = parseInt(e.target.dataset.paso);
            mostrarSeccion();
            botonesPaginador();
        });
    });
}

function botonesPaginador() {
    const btnAnterior = document.querySelector("#anterior");
    const btnSiguiente = document.querySelector("#siguiente");

    if (paso === 1) {
        btnAnterior.classList.add("ocultar");
        btnSiguiente.classList.remove("ocultar");
    } else if (paso === pasoFinal) {
        btnAnterior.classList.remove("ocultar");
        btnSiguiente.classList.add("ocultar");
        mostrarResumen();
    } else {
        btnAnterior.classList.remove("ocultar");
        btnSiguiente.classList.remove("ocultar");
    }
    mostrarSeccion();
}

function paginaAnterior() {
    document.querySelector("#anterior").addEventListener("click", () => {
        paso = paso <= pasoInicial ? pasoInicial : paso - 1;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    document.querySelector("#siguiente").addEventListener("click", () => {
        paso = paso >= pasoFinal ? pasoFinal : paso + 1;
        botonesPaginador();
    });
}

// ---------- API Servicios ----------
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

        const nombreServicio = document.createElement("P");
        nombreServicio.classList.add("nombre-servicio");
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement("P");
        precioServicio.classList.add("precio-servicio");
        precioServicio.textContent = `$${precio}`;

        const servicioDiv = document.createElement("DIV");
        servicioDiv.classList.add("servicio");
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function () {
            seleccionarServicio(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

        document.querySelector("#servicios").appendChild(servicioDiv);
    });
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const { servicios } = cita;

    const divServicio = document.querySelector(`[data-id-servicio='${id}']`);

    if (servicios.some(agregado => agregado.id === id)) {
        cita.servicios = servicios.filter(s => s.id !== id);
        divServicio.classList.remove("seleccionado");
    } else {
        cita.servicios = [...servicios, servicio];
        divServicio.classList.add("seleccionado");
    }
}

// ---------- Datos del cliente ----------
function idCliente() {
    cita.id = document.querySelector("#id").value;
}
function nombreCliente() {
    cita.nombre = document.querySelector("#nombre").value;
}

// ---------- Fecha y Hora ----------
function seleccionarFecha() {
    document.querySelector("#fecha").addEventListener("input", e => {
        const dia = new Date(e.target.value).getUTCDay();
        if ([6, 0].includes(dia)) {
            e.target.value = "";
            mostrarAlerta("Fines de semana no permitidos", "error", ".formulario");
        } else {
            cita.fecha = e.target.value;
        }
    });
}

function seleccionarHora() {
    document.querySelector("#hora").addEventListener("input", e => {
        const horaCita = e.target.value;
        const hora = parseInt(horaCita.split(":")[0]);
        if (hora < 10 || hora > 18) {
            e.target.value = "";
            mostrarAlerta("Hora no válida", "error", ".formulario");
        } else {
            cita.hora = horaCita;
        }
    });
}

// ---------- Paso 2: Pago ----------
function manejarPago() {
    const btnPagar = document.querySelector("#btn-pagar");
    if (!btnPagar) return;

    btnPagar.addEventListener("click", async () => {
        // calcula el total de los servicios seleccionados
        const total = cita.servicios.reduce((sum, s) => sum + Number(s.precio), 0);
        const metodo = document.querySelector("#pago-metodo").value;

        try {
            const res = await fetch(`${location.origin}/api/pagos/crear`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    cita_id: 0, // la cita real se crea en el último paso
                    usuario_id: cita.id,
                    monto: total,
                    metodo: metodo
                })
            });
            const json = await res.json();
            if (json.success) {
                cita.pago = {
                    referencia: json.referencia || json.pago_id,
                    monto: total,
                    metodo: metodo
                };
                mostrarAlerta("Pago registrado correctamente", "exito", "#paso-2");
                // avanzar al paso 3
                paso = 3;
                botonesPaginador();
            } else {
                mostrarAlerta("Error al procesar el pago", "error", "#paso-2", false);
            }
        } catch (err) {
            mostrarAlerta("Error de conexión en el pago", "error", "#paso-2", false);
        }
    });
}

// ---------- Alertas ----------
function mostrarAlerta(mensaje, tipo, selector, desaparecer = true) {
    const alertaPrevia = document.querySelector(".alerta");
    if (alertaPrevia) alertaPrevia.remove();

    const alerta = document.createElement("DIV");
    alerta.textContent = mensaje;
    alerta.classList.add("alerta", tipo);
    document.querySelector(selector).appendChild(alerta);

    if (desaparecer) {
        setTimeout(() => alerta.remove(), 3000);
    }
}

// ---------- Resumen (Paso 4) ----------
function mostrarResumen() {
    const resumen = document.querySelector(".contenido-resumen");
    while (resumen.firstChild) resumen.removeChild(resumen.firstChild);

    if (
        Object.values(cita).includes("") ||
        cita.servicios.length === 0 ||
        !cita.pago.referencia
    ) {
        mostrarAlerta("Faltan datos de servicio, pago, fecha u hora", "error", ".contenido-resumen", false);
        return;
    }

    const { nombre, fecha, hora, servicios, pago } = cita;

    const headingServicios = document.createElement("H3");
    headingServicios.textContent = "Resumen de Servicios";
    resumen.appendChild(headingServicios);

    servicios.forEach(s => {
        const cont = document.createElement("DIV");
        cont.classList.add("contenedor-servicio");

        const pNombre = document.createElement("P");
        pNombre.textContent = s.nombre;

        const pPrecio = document.createElement("P");
        pPrecio.innerHTML = `<span>Precio:</span> $${s.precio}`;

        cont.appendChild(pNombre);
        cont.appendChild(pPrecio);
        resumen.appendChild(cont);
    });

    const headingCita = document.createElement("H3");
    headingCita.textContent = "Resumen de Cita";
    resumen.appendChild(headingCita);

    const pNombre = document.createElement("P");
    pNombre.innerHTML = `<span>Nombre:</span> ${nombre}`;

    const fechaObj = new Date(fecha);
    const fechaLocal = fechaObj.toLocaleDateString("es-MX", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
    });
    const pFecha = document.createElement("P");
    pFecha.innerHTML = `<span>Fecha:</span> ${fechaLocal}`;

    const pHora = document.createElement("P");
    pHora.innerHTML = `<span>Hora:</span> ${hora} hrs`;

    const headingPago = document.createElement("H3");
    headingPago.textContent = "Pago";
    const pPago = document.createElement("P");
    pPago.innerHTML = `<span>Método:</span> ${pago.metodo} — <span>Monto:</span> $${pago.monto}`;

    const btnReservar = document.createElement("BUTTON");
    btnReservar.classList.add("boton");
    btnReservar.textContent = "Confirmar Cita";
    btnReservar.onclick = reservarCita;

    resumen.appendChild(pNombre);
    resumen.appendChild(pFecha);
    resumen.appendChild(pHora);
    resumen.appendChild(headingPago);
    resumen.appendChild(pPago);
    resumen.appendChild(btnReservar);
}

// ---------- Guardar cita ----------
async function reservarCita() {
    const { nombre, fecha, hora, servicios, id } = cita;
    const serviciosId = servicios.map(s => s.id);
    const datos = new FormData();
    datos.append("fecha", fecha);
    datos.append("hora", hora);
    datos.append("usuarioId", id);
    datos.append("servicios", serviciosId);

    try {
        const url = `${location.origin}/api/citas`;
        const respuesta = await fetch(url, {
            method: "POST",
            body: datos
        });

        const resultado = await respuesta.json();
        if (resultado.resultado) {
            Swal.fire({
                icon: "success",
                title: "Cita Creada",
                text: "Tu cita fue creada correctamente",
                button: "OK"
            }).then(() => setTimeout(() => window.location.reload(), 3000));
        } else {
            throw new Error("Error en el servidor");
        }
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Hubo un error al guardar la cita",
            button: "OK"
        });
    }
}
