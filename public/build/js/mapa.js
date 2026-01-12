document.addEventListener('DOMContentLoaded', function() {
    iniciarMapa();
});

function iniciarMapa() {
    // 1. DEFINIR COORDENADAS (Latitud, Longitud)
    // Ejemplo: Coordenadas del Centro de León, Gto. (Cámbialas por las de tu negocio)
    const lat = 21.122378; 
    const lng = -101.682627;
    const zoom = 16; // Nivel de acercamiento (15-17 es bueno para calles)

    // 2. INICIALIZAR MAPA
    // 'mapa' es el id del div en el HTML
    const map = L.map('mapa').setView([lat, lng], zoom);

    // 3. CARGAR LA CAPA DE IMÁGENES (OpenStreetMap - Gratis)
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // 4. AGREGAR EL PIN (MARCADOR)
    L.marker([lat, lng]).addTo(map)
        .bindPopup(`
            <h3 style="margin:0;">Barbería & Salón</h3>
            <p style="margin:5px 0 0;">¡Aquí te esperamos!</p>
        `)
        .openPopup();
}