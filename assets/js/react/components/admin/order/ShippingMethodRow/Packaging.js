import { useEffect, useState } from 'react';
import PackagingModal from './PackagingModal';
const __ = wp.i18n.__;

const Packaging = ({ packaging, products, onPackagingUpdated }) => {

    const [currentPackaging, setCurrentPackaging] = useState(packaging || []);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const openPackagingModal = (e) => {
        e.preventDefault();
        setIsModalOpen(true);
    };

    const closePackagingModal = (updated) => {
        setIsModalOpen(false);
        if (updated) {
            onPackagingUpdated();
        }
    }

    return (
        <div className="packaging-row">
            <h3>{__('Package details')}</h3>
            <div className="packaging-details">
                <p>{currentPackaging.map(pkg => `${pkg.itemNumber}x${pkg.containerType} [${pkg.width}x${pkg.height}x${pkg.length}]`).join(', ')}</p>
            </div>
            <div className="packaging-change-button-container">
                <a href="#" className="hges-button edit-order-button" onClick={openPackagingModal}>Change Packaging and select a new shipping option</a>
            </div>
            <PackagingModal
                isOpen={isModalOpen}
                onClose={closePackagingModal}
                currentPackaging={currentPackaging}
                products={products}
                onChange={(newPackaging) => {
                    setCurrentPackaging(newPackaging);
                }}
            />
        </div>
    );
};

export default Packaging;