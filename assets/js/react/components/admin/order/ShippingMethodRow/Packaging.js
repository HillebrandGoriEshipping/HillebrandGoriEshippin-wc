import { useEffect, useState } from 'react';
import apiClient from '../../../../../apiClient';
import PackagingModal from './PackagingModal';

const Packaging = ({ packaging, products, onChange }) => {

    const [currentPackaging, setCurrentPackaging] = useState(packaging || []);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const openPackagingModal = (e) => {
        e.preventDefault();
        setIsModalOpen(true);
    };

    return (
        <div className="packaging-row">
        <h3>Packaging</h3>
            <div className="packaging-details">
                <h4>Packaging applied :</h4>
                <p>3 x [3 magnums, 42x25x18]</p>
                <a href="javascript:void(0);" onClick={openPackagingModal}>Change Packaging</a>
            </div>
            <PackagingModal
                isOpen={isModalOpen}
                currentPackaging={currentPackaging}
                products={products}
                onChange={(newPackaging) => {
                    setCurrentPackaging(newPackaging);
                    onChange(newPackaging);
                }}
            />
        </div>
    );
};

export default Packaging;