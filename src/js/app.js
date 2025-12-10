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

// Variable global para almacenar las citas ocupadas del día
let citasDelDia = [];

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion(); 
    tabs(); 
    botonesPaginador(); 
    paginaSiguiente(); 
    paginaAnterior();

    consultarAPI(); // Carga los servicios del backend

    idCliente(); 
    nombreCliente(); 
    
    seleccionarFecha(); // Valida fines de semana y descarga citas ocupadas
    seleccionarHora();  // Valida horario comercial y COLISIÓN DE 15 MINUTOS

    mostrarResumen(); 
}

function mostrarSeccion() {
    // 1. Ocultar sección anterior
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // 2. Mostrar sección actual
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // 3. Resaltar tab actual
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
        mostrarResumen();
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

function nombreCliente() {
    cita.nombre = document.querySelector('#nombre').value;
}

// ==========================================
// LÓGICA DE FECHAS Y CITAS OCUPADAS
// ==========================================

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    
    inputFecha.addEventListener('input', function(e) {
        // Obtenemos el día de la semana (0 = Domingo, 6 = Sábado)
        const dia = new Date(e.target.value).getUTCDay();

        // 1. Bloquear Fines de Semana
        if( [6, 0].includes(dia) ) {
            e.target.value = '';
            mostrarAlerta('Fines de semana no permitidos', 'error', '.formulario');
        } else {
            // Fecha válida: la guardamos y buscamos las citas de ese día
            cita.fecha = e.target.value;
            buscarCitasPorFecha(cita.fecha);
        }
    });
}

// Variable global (asegúrate que esté al inicio del archivo)
 

async function buscarCitasPorFecha(fecha) {
    try {
        // 1. Definir la URL. Intenta ambas rutas por si acaso.
        // Si usaste el código anterior, debería ser /api/citas o /api/ms/citas
        const url = `/api/citas?fecha=${fecha}`; 
        
        console.log(`📡 Consultando API: ${url}`);

        const respuesta = await fetch(url);
        
        // 2. Verificar si el servidor respondió bien (Status 200)
        if(!respuesta.ok) {
            console.error('❌ Error en el servidor. Status:', respuesta.status);
            throw new Error('Error al conectar con la API');
        }

        const resultado = await respuesta.json();
        console.log("📦 Datos recibidos del servidor:", resultado);

        // 3. Guardar las citas (Manejo robusto de formatos)
        // Si la API devuelve {agenda: [...]} usamos eso, si no, usamos resultado directo
        if(resultado.agenda) {
            citasDelDia = resultado.agenda;
        } else if(Array.isArray(resultado)) {
            citasDelDia = resultado;
        } else {
            citasDelDia = []; // Formato desconocido
        }
        
        console.log("✅ Citas guardadas en memoria:", citasDelDia);

    } catch (error) {
        console.error('❌ Error grave en JS:', error);
        citasDelDia = []; // Limpiamos para evitar errores
    }
}

// ==========================================
// LÓGICA DE HORA Y COLISIONES (15 MINS)
// ==========================================

function seleccionarHora() {
    const inputHora = document.querySelector('#hora');
    
    inputHora.addEventListener('input', function(e) {
        const horaUsuario = e.target.value;
        const hora = horaUsuario.split(":")[0];

        // 1. VALIDACIÓN: Horario Comercial (9:00 a 20:00)
        if(hora < 9 || hora > 20) {
            e.target.value = '';
            mostrarAlerta('Hora no válida. Abrimos de 9:00 a 20:00', 'error', '.formulario');
            return;
        }

        // 2. VALIDACIÓN: Colisión de 15 minutos (Matemática Pura)
        const choca = citasDelDia.some(citaBD => {
            
            // Convertimos Hora BD a Minutos Totales (ej: 10:30 -> 630 min)
            const horaBDArr = citaBD.hora.split(":"); 
            const minutosBD = (parseInt(horaBDArr[0]) * 60) + parseInt(horaBDArr[1]);

            // Convertimos Hora Usuario a Minutos Totales
            const horaUserArr = horaUsuario.split(":");
            const minutosUser = (parseInt(horaUserArr[0]) * 60) + parseInt(horaUserArr[1]);

            // Diferencia Absoluta
            const diferencia = Math.abs(minutosBD - minutosUser);

            // Si la diferencia es menor a 15 min, es colisión
            return diferencia < 15;
        });

        if(choca) {
            e.target.value = ''; // Reseteamos el input
            mostrarAlerta('Horario ocupado. Debe haber 15 mins de diferencia.', 'error', '.formulario');
        } else {
            // Todo correcto
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

    while(resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    if(Object.values(cita).includes('') || cita.servicios.length === 0 ) {
        mostrarAlerta('Faltan datos de Servicios, Fecha u Hora', 'error', '.contenido-resumen', false);
        return;
    }

    const { nombre, fecha, hora, servicios } = cita;

    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

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

    const headingCita = document.createElement('H3');
    headingCita.textContent = 'Resumen de Cita';
    resumen.appendChild(headingCita);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Cliente:</span> ${nombre}`;

    // Formatear Fecha
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
    const idServicios = servicios.map( servicio => servicio.id );

    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('usuarioId', id);
    datos.append('servicios', idServicios);

    try {
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
                }, 1500);
            })
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.error || 'Hubo un error al guardar'
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