
import SVG from './SVG';
import leafletMap from '../leafletMap';
import apiClient from '../apiClient';
import { useState } from '@wordpress/element';
const { select } = window.wp.data;
const cartStore = select("wc/store/cart");
import dayjs from "dayjs";

const PickupPointsMap = () =>{

    let map = null;
    const [pickupPoints, setPickupPoints] = useState(null);

    window.addEventListener('hges:show-pickup-points-map', function(e) {
        openModal(e.detail.rate);
    });

    window.addEventListener('hges:hide-pickup-points-map', function(e) {
        closeModal();
    });

    const closeModal = (e) => {
        e.preventDefault();
        const modal = document.querySelector('#pickup-points-map-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    const openModal = async (rate) => {
        const modal = document.querySelector('#pickup-points-map-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        
        // init the map if not already initialized
        if (!map && document.querySelector('#pickup-points-map')) {
            map = leafletMap.init(document.querySelector('#pickup-points-map'));
        }

        const shippingAddress = cartStore.getCustomerData().shippingAddress;
        const pickupPointList = await apiClient.getFromProxy(
            '/pickup-points',
            { 
                street: shippingAddress.address_1,
                zipCode: shippingAddress.postcode,
                city: shippingAddress.city,
                shipmentDate: dayjs(rate.pickupDate).format('DD/MM/YYYY'),
                country: shippingAddress.country,
                productCode: 86
            }
        );
        
        map.clearMarkers();
        pickupPointList.forEach(relay => {
            leafletMap.addMarker(
                relay.latitude,
                relay.longitude, 
                {...relay}
            );
        });
        map.updateMarkers();
        map.setView(pickupPointList[0].latitude, pickupPointList[0].longitude, 14);
        setPickupPoints(pickupPointList);
    }

    return (
        <div id="pickup-points-map-modal" className="modal hidden">
            <div className="modal__content">
                <button className="modal__close" onClick={closeModal}>  
                    {/* hges object is injected from the Assets\Scripts class */}
                    <SVG src={hges.assetsUrl + 'img/close.svg'} className="modal__close-icon" />
                </button>
                <div className="map-container" id="pickup-points-map"></div>
                <div className="modal__side" id="pickup-points-list">
                    { pickupPoints && pickupPoints.map((relay, index) => (
                        <div className="pickup-point" key={index}>
                            <div className="pickup-point__title">{relay.name}</div>
                            <div className="pickup-point__distance">{relay.distance}m</div>
                        </div>
                    )) }
                </div>   
            </div>
        </div>
    )
}

export default PickupPointsMap;