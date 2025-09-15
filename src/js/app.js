document.addEventListener('DOMContentLoaded', function() {
    let paso = 1;
    const maxPaso = 4;

    function mostrarPaso() {
        for (let i = 1; i <= maxPaso; i++) {
            document.getElementById(`paso-${i}`).classList.toggle('ocultar', i !== paso);
        }
    }

    // Simular pago en Paso 2
    const btnPagar = document.getElementById('btn-pagar');
    btnPagar.addEventListener('click', async () => {
        const monto = calcularMontoSeleccionado(); // tu función para obtener el monto del paso 1
        const metodo = document.getElementById('pago-metodo').value;

        const body = {
            cita_id: 0, // si aún no existe, 0, luego actualizas
            usuario_id: usuarioActualId, // obtén de sesión
            monto: monto,
            metodo: metodo
        };

        const res = await fetch('/api/pagos/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const json = await res.json();
        const mensaje = json.success ? 
            `Pago registrado. Ref: ${json.referencia || json.pago_id}` : 
            'Error en el pago';
        document.getElementById('pago-resultado').textContent = mensaje;

        if (json.success) {
            paso = 3;
            mostrarPaso();
        }
    });

    // Siguiente paso cuando se confirma la cita
    document.getElementById('confirmar-cita').addEventListener('click', () => {
        // Aquí envías datos finales de cita a /api/citas
        // usando fetch, y luego muestras mensaje final
    });

    mostrarPaso();
});
