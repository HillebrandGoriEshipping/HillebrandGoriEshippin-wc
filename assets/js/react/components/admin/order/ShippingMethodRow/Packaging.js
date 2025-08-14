import { useEffect, useState } from 'react';
import PackagingModal from './PackagingModal';

const Packaging = ({ packaging, products }) => {

    const [currentPackaging, setCurrentPackaging] = useState(packaging || []);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const openPackagingModal = (e) => {
        e.preventDefault();
        setIsModalOpen(true);
    };

    const closePackagingModal = () => {
        setIsModalOpen(false);
        window.location.reload();
    }

    return (
        <div className="packaging-row">
        <h3>Packaging</h3>
            <div className="packaging-details">
                <p>{currentPackaging.map(pkg => `${pkg.itemNumber}x${pkg.containerType} [${pkg.width}x${pkg.height}x${pkg.length}]`).join(', ')}</p>
                <a href="#" onClick={openPackagingModal}>Change Packaging</a>
            </div>
            <PackagingModal
                isOpen={isModalOpen}
                onClose={closePackagingModal}
                currentPackaging={currentPackaging}
                products={products}
                onChange={(newPackaging) => {
                    console.log('Updated packaging:', newPackaging);
                    setCurrentPackaging(newPackaging);
                }}
            />
        </div>
    );
};

export default Packaging;