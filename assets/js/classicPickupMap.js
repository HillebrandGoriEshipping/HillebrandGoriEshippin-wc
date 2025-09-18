document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('#pickup-points-map-modal');
    const mapContainer = document.querySelector('#pickup-points-map');
    const closeBtn = document.querySelector('#close-pickup-map-modal');
    const popupTemplate = document.querySelector('#marker-popup-template');
    const spinner = document.querySelector('#pickup-points-spinner');
    const listContainer = document.querySelector('#pickup-points-list');
    const selectBtn = document.querySelector('#select-pickup-point');
    const selectedPointDiv = document.querySelector('#selected-pickup-point');
    if (selectedPointDiv) {
        selectedPointDiv.classList.add('hidden');
    }
    const nameSpan = document.querySelector('#selected-pickup-point-name');
    const addressSpan = document.querySelector('#selected-pickup-point-address');

    let mapInstance = null;
    let pickupPoints = [];
    let currentPickupPoint = null;
    let currentRateChecksum = null;

    async function loadPickupPoints() {
        if (!mapInstance) return;

        const form = document.querySelector('form.checkout');
        const formData = new FormData(form);

        spinner.classList.remove('hidden');

        try {
            const res = await apiClient.get(
                window.hges.ajaxUrl,
                {
                    action: 'hges_get_pickup_points',
                    checksum: currentRateChecksum
                }
            );

            const points = res.data || [];
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
        currentRateChecksum = e.currentTarget.closest('.hges-shipping-method').dataset.checksum;
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

        nameSpan.textContent = currentPickupPoint.name;
        addressSpan.textContent = currentPickupPoint.addLine1 + ', ' + currentPickupPoint.city;
        selectedPointDiv.classList.remove('hidden');
        localStorage.setItem('hges_current_pickup_point', JSON.stringify(currentPickupPoint));
        closeModal(e);
    }

    closeBtn?.addEventListener('click', closeModal);
    selectBtn?.addEventListener('click', selectPickupPoint);

    window.openModal = openModal;
});

function showSavedPickupPoint() {
    const saved = localStorage.getItem('hges_current_pickup_point');
    if (!saved) return;

    try {
        const parsed = JSON.parse(saved);
        const nameSpan = document.querySelector('#selected-pickup-point-name');
        const container = document.querySelector('#selected-pickup-point');

        if (nameSpan && container) {
            nameSpan.textContent = parsed.name;
            container.classList.remove('hidden');
        }
    } catch (err) {
        console.warn('Erreur lecture point relais localStorage', err);
    }
}

jQuery(document.body).on('updated_checkout', function () {
    showSavedPickupPoint();
});