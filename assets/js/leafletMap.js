import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

export default {
    id: null,
    parentElement: null,
    map: null,
    markers: [],
    options: {
        center: [48, 10],
        zoom: 5,
        layers: []
    },
    init(parentElement, options, markers) {
        if (!parentElement) {
            throw new Error('Parent element is required');
        }
        const mapId = Math.random().toString(36).substring(2, 9);
        if (!parentElement.id) {
            const newId = `map-${mapId}`;
            parentElement.id = newId;
        }

        this.id = mapId;
        this.parentElement = parentElement;
        this.options = options || {};
        this.markers = markers || [];
        if (!this.map) {
            this.map = L.map(parentElement.id, {
                center: options?.center || [48, 10],
                zoom: options?.zoom || 5,
                layers: options?.layers || []
            });
        }
    
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(this.map);

        this.updateMarkers();
        return this;
    },
    addMarker(lat, lng, options) {
        const marker = {lat, lng, options};
        marker.options.icon = L.icon({
            iconUrl: hges.assetsUrl + 'img/marker.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [-3, -76],
        });

        this.markers.push(marker);
        return marker;
    },
    removeMarker(marker) {
        this.map.removeLayer(marker);
        this.markers = this.markers.filter(m => m !== marker);
    },
    clearMarkers() {
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];
    },
    updateMarkers() {
        this.markers.forEach(marker => {
            const newMarker = L.marker([marker.lat, marker.lng], marker.options);
            newMarker.addTo(this.map);
            const markerPopupTemplate = document.querySelector('#marker-popup-template');

            if (markerPopupTemplate) {
                const popupContent = markerPopupTemplate.innerHTML
                    .replace('{{title}}', marker.options.title || '')
                    .replace('{{distance}}', marker.options.distance || '');
                newMarker.bindPopup(popupContent);
            } else {
                newMarker.bindPopup(marker.options.title || '');
            }
            
            newMarker.on('click', () => {
                this.parentElement.dispatchEvent(new CustomEvent('LeafletMarkerClick', {
                    detail: { marker }
                }));
            });
        });
    },
    setView(lat, lon, zoom) {
        this.map.setView([lat, lon], zoom);
    },
    getMap() {
        return this.map;
    },
    getMarkers() {
        return this.markers;
    },
    getParentElement() {
        return this.parentElement;
    },
    getOptions() {
        return this.options;
    }
}