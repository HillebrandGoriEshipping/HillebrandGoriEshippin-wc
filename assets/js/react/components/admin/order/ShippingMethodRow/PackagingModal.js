import { useEffect, useState } from 'react';
import apiClient from '../../../../../apiClient';
import PackagingOptionItem from './PackagingOptionItem';
import { __ } from '@wordpress/i18n'

const PackagingModal = ({ currentPackaging, products, onChange }) => {

    const [packagingOptions, setPackagingOptions] = useState([]);
    const [packages, setPackages] = useState([]);
    const initialProductsNumberByType = Object.keys(products).reduce((acc, key) => {
        const product = products[key];
        const type = product.meta_data.find(item => item.key === 'packaging')?.value || 'bottle';
        if (!acc[type]) {
            acc[type] = 0;
        }
        acc[type] += product.quantity || 0;
        return acc;
    }, {});

    const [productsNumberByType, setProductsNumberByType] = useState(initialProductsNumberByType);

    useEffect(() => {
        const fetchPackagingOptions = async () => {
            try {
                const response = await apiClient.get(
                    window.hges.ajaxUrl,
                    { action: 'hges_get_packaging_options' }
                );
                setPackagingOptions(response.packagings);
            } catch (error) {
                console.error("Error fetching packaging options:", error);
            }
        };

        fetchPackagingOptions();
    }, []);

    const updateCurrentPackaging = (itemIndex, packagingOption) => {
        setPackages(prevPackages => {
            const updatedPackages = [...prevPackages];
            const packageToUpdate = updatedPackages.find(pkg => pkg.index === itemIndex);
            if (packageToUpdate) {
                Object.assign(packageToUpdate, packagingOption);
            }
            return updatedPackages;
        });
    };

    useEffect(() => {
        updateProductsToDispatch();
    }, [packages]);

    const updateProductsToDispatch = () => {
        const updatedProductsNumberByType = { ...initialProductsNumberByType };
        for (const type in updatedProductsNumberByType) {
            packages.forEach((pkg) => {
                if (pkg.containerType === type) {
                    updatedProductsNumberByType[type] -= pkg.itemNumber;
                }
            });
        }
        setProductsNumberByType(updatedProductsNumberByType);
    };

    const createPackage = () => {
        setPackages([...packages, { index: packages.length + 1, itemNumber: 0, width: 0, height: 0, length: 0, weight: { still: 0, sparkling: 0 } }]);
    };

    return (
        <div id="packaging-modal" className="modal">
            <div className="modal__content">
                <span className="modal__close" >&times;</span>
                <div className="modal__section">
                    <h3>{ __('Products to dispatch') }</h3>
                    <div>
                        {Object.entries(productsNumberByType).map(([type, quantity], index) => (
                            <span key={type}>{quantity} x <strong>{type}</strong>{index < Object.entries(productsNumberByType).length - 1 ? ', ' : ''}</span>
                        ))}
                    </div>
                </div>
                <div className="modal__section">
                    <h3>{ __('Packages') }</h3>
                    <div className="packaging-option-list">
                        {packages.map((packageItem) => (
                            <PackagingOptionItem key={packageItem.index} packagingOptions={packagingOptions} packageItem={packageItem} onSelect={(selectedOption) => updateCurrentPackaging(packageItem.index, selectedOption)} />
                        ))}
                        <div className="plus-round-button" onClick={createPackage}>
                            <span className="dashicons dashicons-plus"></span>
                        </div>
                    </div>
                </div>
                <div className="modal__footer">
                    <button onClick={() => onChange(null)}>Close</button>
                </div>
            </div>
        </div>
    );

};

export default PackagingModal;
