document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    buscarPorFecha(); // Escucha cambios en el input de fecha
}

// ---------------------------------------------------------
// 1. VER CITAS (GET)
// ---------------------------------------------------------
function buscarPorFecha() {
    const fechaInput = document.querySelector('#fecha');
    
    // Si no existe el input, detenemos la ejecución (para evitar errores en otras pág)
    if(!fechaInput) return;

    // Escuchar cuando el usuario cambia la fecha
    fechaInput.addEventListener('input', async function(e) {
        const fechaSeleccionada = e.target.value;

        try {
            // Consumo del Microservicio GET
            const url = `/api/citas?fecha=${fechaSeleccionada}`;
            const respuesta = await fetch(url);
            const citas = await respuesta.json();
            
            mostrarCitas(citas); // Función auxiliar para pintar el HTML

        } catch (error) {
            console.log(error);
        }
    });
}

function mostrarCitas(citas) {
    // Limpiar listado anterior
    const citasContainer = document.querySelector('.citas');
    citasContainer.innerHTML = '';

    if(citas.length === 0) {
        citasContainer.innerHTML = '<p class="alerta error">No hay citas en esta fecha</p>';
        return;
    }

    // Generar HTML por cada cita
    citas.forEach(cita => {
        const { id, hora, cliente, servicios } = cita; // Asegúrate de que tu API devuelva esto

        const divCita = document.createElement('DIV');
        divCita.classList.add('cita');

        divCita.innerHTML = `
            <p>ID: <span>${id}</span></p>
            <p>Hora: <span>${hora}</span></p>
            <p>Cliente: <span>${cliente}</span></p>
            <button class="boton-eliminar" onclick="eliminarCita(${id})">Eliminar</button>
        `;

        citasContainer.appendChild(divCita);
    });
}

// ---------------------------------------------------------
// 2. GUARDAR CITA MANUALMENTE (POST)
// ---------------------------------------------------------
// Esta función se debe asignar al evento 'onsubmit' del formulario de crear cita
async function guardarCitaAdmin(e) {
    e.preventDefault(); // Evitar recarga

    const usuarioId = document.querySelector('#usuario').value;
    const cliente = document.querySelector('#cliente').value;
    const fecha = document.querySelector('#fecha-crear').value;
    const hora = document.querySelector('#hora-crear').value;
    const servicios = document.querySelector('#servicios').value; // Ej: "1,2,3"

    // Validar vacíos
    if([usuarioId, cliente, fecha, hora, servicios].includes('')) {
        Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
        return;
    }

    const datos = new FormData();
    datos.append('usuarioId', usuarioId);
    datos.append('cliente', cliente);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('servicios', servicios);

    try {
        const url = '/api/citas';
        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if(resultado.resultado) {
            Swal.fire('Creada', 'La cita se creó correctamente', 'success')
                .then(() => window.location.reload());
        } else {
            Swal.fire('Error', resultado.error || 'No se pudo guardar', 'error');
        }

    } catch (error) {
        Swal.fire('Error', 'Error de conexión', 'error');
    }
}

// ---------------------------------------------------------
// 3. ELIMINAR CITA (POST)
// ---------------------------------------------------------
async function eliminarCita(id) {
    
    const confirmacion = await Swal.fire({
        title: '¿Eliminar Cita?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar'
    });

    if (confirmacion.isConfirmed) {
        const datos = new FormData();
        datos.append('id', id);

        try {
            const url = '/api/citas/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();

            if(resultado.resultado) {
                Swal.fire('Eliminado', 'La cita fue eliminada.', 'success')
                    .then(() => {
                        // Opcional: Recargar o quitar el elemento del DOM sin recargar
                         window.location.reload(); 
                    });
            } else {
                 Swal.fire('Error', resultado.error || 'No se pudo eliminar', 'error');
            }
        } catch (error) {
            console.log(error);
        }
    }
}