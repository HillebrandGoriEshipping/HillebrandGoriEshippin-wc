document.addEventListener('DOMContentLoaded', () => {

  document.addEventListener('click', function (e) {
    const button = e.target.closest('.pickup-point-button');
    const closeBtn = e.target.closest('#close-modal');

    if (button) {
      e.preventDefault();

      const modal = document.getElementById('pickup-points-map-modal');
      const mapContainer = document.getElementById('pickup-points-map');

      if (!modal || !mapContainer) return;

      modal.classList.remove('hidden');

      if (!window._pickupMapInstance) {
        window._pickupMapInstance = window.leafletMap.init(mapContainer, {
          center: [48.8566, 2.3522],
          zoom: 12
        });

        window.leafletMap.addMarker(48.8566, 2.3522, {
          popupContent: 'Point relais à Paris'
        });
      }
    }

    if (closeBtn) {
      e.preventDefault();

      const modal = document.getElementById('pickup-points-map-modal');
      if (modal) {
        modal.classList.add('hidden');
      }
    }
  });
});
