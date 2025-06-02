window.openModal = function(e) {
  e.preventDefault();
  console.log('openModal triggered');
  
  const modal = document.getElementById('pickup-points-map-modal');
  const mapContainer = document.getElementById('pickup-points-map');
  const spinner = document.getElementById('pickup-points-spinner');
  const listContainer = document.getElementById('pickup-points-list');
  const closeBtn = document.getElementById('close-pickup-map-modal');
  
  modal.classList.remove('hidden');

  if (!window.leafletMapInstance && mapContainer) {
    // Initialize the Leaflet map
    window.leafletMapInstance = L.map(mapContainer).setView([48.8566, 2.3522], 12);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
      maxZoom: 19,
    }).addTo(window.leafletMapInstance);
  }

  // Simulate loading data
  spinner.style.display = 'block';
  setTimeout(() => {
    spinner.style.display = 'none';

    // Example of adding a marker
    listContainer.innerHTML = `
      <div class="pickup-point">
        <div class="pickup-point__title">
          <a href="#" onclick="alert('Pickup point clicked!')">Pickup Point #1</a>
        </div>
        <div class="pickup-point__address">123 rue Exemple, Paris</div>
        <div class="pickup-point__distance">500m</div>
      </div>
    `;
  }, 1000);

  if (closeBtn) {
    closeBtn.onclick = function(ev) {
      ev.preventDefault();
      modal.classList.add('hidden');
    };
  }
};
