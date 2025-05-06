
import SVG from './SVG';

const PickupPointsMap = () =>{

    window.addEventListener('hges:show-pickup-points-map', function(e) {
        openModal(e.detail.rate);
    });

    window.addEventListener('hges:hide-pickup-points-map', function(e) {
        closeModal();
    });

    const closeModal = () => {
        const modal = document.querySelector('#pickup-points-map-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    const openModal = (rate) => {
        const modal = document.querySelector('#pickup-points-map-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        console.log('opened modal', rate);
    }

    return (
        <div id="pickup-points-map-modal" className="modal hidden">
            <div className="modal__content">
                <button className="modal__close" onClick={closeModal}>
                    <SVG src={'../img/close.svg'} className="modal__close-icon" />
                </button>
                MAP
            </div>
        </div>
    )
}


export default PickupPointsMap;