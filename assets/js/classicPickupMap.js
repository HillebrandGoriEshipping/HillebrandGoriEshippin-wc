document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('pickup-points-map-modal');
  const mapContainer = document.getElementById('pickup-points-map');
  const closeBtn = document.getElementById('close-pickup-map-modal');
  const popupTemplate = document.getElementById('marker-popup-template');
  const spinner = document.getElementById('pickup-points-spinner');
  const listContainer = document.getElementById('pickup-points-list');
  const selectBtn = document.getElementById('select-pickup-point');

  let mapInstance = null;
  let pickupPoints = [];
  let currentPickupPoint = null;
  let currentRate = null;

  async function loadPickupPoints() {
    if (!mapInstance) return;

    const form = document.querySelector('form.checkout');
    const formData = new FormData(form);

    const shippingAddress = {
      address_1: formData.get('shipping_address_1'),
      postcode: formData.get('shipping_postcode'),
      city: formData.get('shipping_city'),
      country: formData.get('shipping_country')
    };

    spinner.classList.remove('hidden');

    try {
      const res = await apiClient.get('/relay/get-chronopost-relay-points', {
          street: shippingAddress.address_1,
          zipCode: shippingAddress.postcode,
          city: shippingAddress.city,
          shipmentDate: "06/06/2025",//dayjs().format('DD/MM/YYYY'),
          country: shippingAddress.country,
          productCode: '86' // adapt this
      });

      const points = res || [];
      mapInstance.clearMarkers();
      listContainer.innerHTML = '';

    points.forEach(p => {
      const markerHTML = popupTemplate.cloneNode(true);
      markerHTML.style.display = 'block';
      markerHTML.querySelector('.marker-popup__title').innerText = p.name;
      markerHTML.querySelector('.marker-popup__address').innerText = p.addLine1;
      markerHTML.querySelector('.marker-popup__distance').innerText = p.distance + 'm';

      const marker = window.leafletMap.addMarker(p.latitude, p.longitude, {
        id: p.id,
        popupContent: markerHTML.innerHTML
      });

      marker.on('click', () => {
        mapInstance.setView(marker.getLatLng(), 16);
        currentPickupPoint = p;
      });

      const div = document.createElement('div');
      div.className = 'pickup-point';
      div.innerHTML = `
          <div class="pickup-point__title">
              <a href="#" data-pickup='${JSON.stringify(p)}'>${p.name}</a>
          </div>
          <div class="pickup-point__address">${p.addLine1}</div>
          <div class="pickup-point__distance">${p.distance}m</div>
      `;
      div.querySelector('a').addEventListener('click', e => {
          e.preventDefault();
          currentPickupPoint = p;
          const marker = window.leafletMap.getMarkers().find(m => m.options.id === p.id);
          if (marker) {
              marker.openPopup();
              mapInstance.setView(marker.getLatLng(), 16);
          }
      });

      listContainer.appendChild(div);
    });

    if (points.length > 0) {
        mapInstance.setView([points[0].latitude, points[0].longitude], 14);
    }

    pickupPoints = points;
    } catch (error) {
      console.error('Erreur lors du chargement des points relais :', error);
    } finally {
      spinner.classList.add('hidden');
    }
  }

  function openModal(e) {
    modal.classList.remove('hidden');

    if (!mapInstance && mapContainer) {
        mapInstance = window.leafletMap.init(mapContainer, {
            center: [48.8566, 2.3522],
            zoom: 12
        });
    }

    loadPickupPoints();
  }

  function closeModal(e) {
    e.preventDefault();
    modal.classList.add('hidden');
  }

  async function selectPickupPoint(e) {
    e.preventDefault();
    if (!currentPickupPoint) return;

    spinner.classList.remove('hidden');

    const input = document.getElementById('hges-pickup-point-data');
    input.value = JSON.stringify(currentPickupPoint);
    closeModal(e);
  }

  // Bind closing and selecting
  closeBtn?.addEventListener('click', closeModal);
  selectBtn?.addEventListener('click', selectPickupPoint);

  // Permet l'appel via onclick
  window.openModal = openModal;
});
