import { useEffect, useState } from 'react';
import PackagingModal from './PackagingModal';

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
        <h3>Packaging</h3>
            <div className="packaging-details">
                <p>{currentPackaging.map(pkg => `${pkg.itemNumber}x${pkg.containerType} [${pkg.width}x${pkg.height}x${pkg.length}]`).join(', ')}</p>
                <a href="#" onClick={openPackagingModal}>Change Packaging and select a new shipping option</a>
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