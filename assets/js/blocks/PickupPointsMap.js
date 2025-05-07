
import SVG from './SVG';
import leafletMap from '../leafletMap';
import apiClient from '../apiClient';
import { useState, useEffect, useRef } from '@wordpress/element';
const { select } = window.wp.data;
const cartStore = select("wc/store/cart");
import dayjs from "dayjs";

const PickupPointsMap = () => {
    const modalRef = useRef(null);
    const mapContainerRef = useRef(null);
    const markerPopupTemplate = useRef(null);

    const [map, setMap] = useState(null);
    const [pickupPoints, setPickupPoints] = useState(null);
    const [showModal, setShowModal] = useState(false);
    const [currentRate, setCurrentRate] = useState(null);
    const [currentPickupPoint, setCurrentPickupPoint] = useState(null);

    const openModal = (e) => {
        setCurrentRate(e.detail.rate);
        modalRef.current.classList.remove('hidden');
        setShowModal(true);
    }

    const closeModal = (e) => {
        e.preventDefault();
        modalRef.current.classList.add('hidden');
        setShowModal(false);
    }

    useEffect(() => {
        window.addEventListener('hges:show-pickup-points-map', openModal);
        window.addEventListener('hges:hide-pickup-points-map', closeModal);

        return () => {
            window.removeEventListener('hges:show-pickup-points-map', openModal);
            window.removeEventListener('hges:hide-pickup-points-map', closeModal);
        };
    }, []);

    useEffect(() => {
        if (showModal && !map && mapContainerRef.current) {
            const m = leafletMap.init(mapContainerRef.current);
            setMap(m);
        }
    }, [showModal, map]);

    useEffect(() => {
        const loadPickupPoints = async () => {

            if (!map || !currentRate || !markerPopupTemplate) return;

            const shippingAddress = cartStore.getCustomerData().shippingAddress;
            const pickupPointList = await apiClient.getFromProxy(
                '/pickup-points',
                {
                    street: shippingAddress.address_1,
                    zipCode: shippingAddress.postcode,
                    city: shippingAddress.city,
                    shipmentDate: dayjs(currentRate.pickupDate).format('DD/MM/YYYY'),
                    country: shippingAddress.country,
                    productCode: 86
                }
            );

            map.clearMarkers();
            pickupPointList.forEach(pickupPoint => {
                const options = { ...pickupPoint };
                markerPopupTemplate.current.querySelector('.marker-popup__title').innerHTML = pickupPoint.name;
                markerPopupTemplate.current.querySelector('.marker-popup__address').innerHTML = pickupPoint.addLine1;
                markerPopupTemplate.current.querySelector('.marker-popup__distance').innerHTML = pickupPoint.distance + 'm';
                options.popupContent = markerPopupTemplate.current.innerHTML;
                const marker = leafletMap.addMarker(
                    pickupPoint.latitude,
                    pickupPoint.longitude,
                    options
                );

                marker.on('click', () => {
                    map.setView(marker.getLatLng(), 16);
                    setCurrentPickupPoint(pickupPoint);
                });
            });

            map.setView([pickupPointList[0].latitude, pickupPointList[0].longitude], 14);
            setPickupPoints(pickupPointList);
        }
        loadPickupPoints(currentRate);
    }, [map, currentRate, markerPopupTemplate]);

    const onItemClick = (e) => {
        e.preventDefault();
        const pickupPoint = e.currentTarget.dataset.pickupPoint;
        const pickupPointData = JSON.parse(pickupPoint);
        setCurrentPickupPoint(pickupPointData);
        const marker = leafletMap.getMarkers().find(m => m.options.id === pickupPointData.id);
        if (marker) {
            marker.openPopup();
            map.setView(marker.getLatLng(), 16);
        }
    }

    const selectThisPickupPoint = (e) => {
        e.preventDefault();
        closeModal(e);
        window.dispatchEvent(new CustomEvent('hges:pickup-points-selected', {
            detail: {
                pickupPoint: currentPickupPoint,
            }
        }));
    };

    return (
        <div id="pickup-points-map-modal" className={`modal ${showModal ? '' : 'hidden'}`} ref={modalRef}>
            <div className="modal__content">
                <button className="modal__close" onClick={closeModal}>
                    {/* hges object is injected from the Assets\Scripts class */}
                    <SVG src={hges.assetsUrl + 'img/close.svg'} className="modal__close-icon" />
                </button>
                <div ref={mapContainerRef} className="map-container" id="pickup-points-map"></div>
                <div className="modal__side" id="pickup-points-list">
                    {pickupPoints && pickupPoints.map((pickupPoint, index) => (
                        <div className="pickup-point" key={index}>
                            <div className="pickup-point__title">
                                <a
                                    href="#"
                                    onClick={onItemClick}
                                    data-pickup-point={JSON.stringify(pickupPoint)}
                                >
                                    {pickupPoint.name}
                                </a>
                            </div>
                            <div className="pickup-point__address">
                                {pickupPoint.address_1}
                            </div>
                            <div className="pickup-point__distance">{pickupPoint.distance}m</div>
                        </div>
                    ))}
                    <button className="pickup-point__close" data-pickup-point-id={1} onClick={selectThisPickupPoint}>Select this pickup point</button>
                </div>
            </div>
            <div style={{ display: 'none' }} className="marker-popup" ref={markerPopupTemplate}>
                <div className="marker-popup__title"></div>
                <div className="marker-popup__address"></div>
                <div className="marker-popup__distance"></div>
            </div>
        </div>
    );
}

export default PickupPointsMap;