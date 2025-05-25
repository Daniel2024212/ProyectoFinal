if(document.querySelector('#mapa')) {
    const lat = 21.131445;
    const lng = -101.668789;
    const zoom = 16;

    const map = L.map('mapa').setView([lat, lng], zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        detectRetina: true
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup(`
            <h2 class="mapa__heading">Salón de Belleza</h2>
            <p class="mapa__texto">Centro de belleza</p>    
        `)
        .openPopup();
}