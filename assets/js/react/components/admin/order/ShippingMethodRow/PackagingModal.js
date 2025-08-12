import { useEffect, useState } from 'react';
import apiClient from '../../../../../apiClient';
import PackagingOptionItem from './PackagingOptionItem';
import { __ } from '@wordpress/i18n'

const PackagingModal = ({ currentPackaging, products, onChange }) => {

    const [packagingOptions, setPackagingOptions] = useState([]);
    const [packages, setPackages] = useState([]);
    console.log('products in PackagingModal:', products);

    useEffect(() => {
        const fetchPackagingOptions = async () => {
            try {
                const response = await apiClient.get(
                    window.hges.ajaxUrl,
                    { action: 'hges_get_packaging_options' }
                );
                console.log("Packaging options fetched:", response);
                setPackagingOptions(response.packagings);
            } catch (error) {
                console.error("Error fetching packaging options:", error);
            }
        };

        fetchPackagingOptions();
    }, []);

    const productsNumberByType = Object.keys(products).reduce((acc, key) => {
        const product = products[key];
        const type = product.meta_data.find(item => item.key === 'packaging')?.value || 'bottle';
        if (!acc[type]) {
            acc[type] = 0;
        }
        acc[type] += product.quantity || 1;
        return acc;
    }, {});

    console.log("Products by type:", productsNumberByType);

    const updateCurrentPackaging = (packagingOption) => {
        console.log("Updated packaging option:", packagingOption);
    };

    const createPackage = () => {
        setPackages([...packages, { id: packages.length + 1, itemNumber: 1, width: 0, height: 0, length: 0, weight: { still: 0, sparkling: 0 } }]);
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
                        {packages.map((packageItem) => (
                            <PackagingOptionItem key={packageItem.id} packagingOptions={packagingOptions} packageItem={packageItem} onSelect={updateCurrentPackaging} />
                        ))}
                        <div className="plus-round-button" onClick={createPackage}>
                            <span className="dashicons dashicons-plus"></span>
                        </div>
                </div>
            </div>
            <button onClick={() => onChange(null)}>Close</button>
        </div>
    );

};

export default PackagingModal;
