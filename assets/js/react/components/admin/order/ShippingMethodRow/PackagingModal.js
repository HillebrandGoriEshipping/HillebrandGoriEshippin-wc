import { useEffect, useState } from 'react';
import apiClient from '../../../../../apiClient';
import PackagingOptionItem from './PackagingOptionItem';
import { __ } from '@wordpress/i18n'
import clsx from 'clsx';

const PackagingModal = ({ currentPackaging, products, onChange, isOpen, onClose }) => {

    if (currentPackaging) {
        currentPackaging.forEach((pkg, index) => {
            if (!pkg.index) {
                pkg.index = index + 1;
            }
        });
    }
    const [packagingOptions, setPackagingOptions] = useState([]);
    const [packages, setPackages] = useState(currentPackaging || []);
    const initialProductsNumberByType = Object.keys(products).reduce((acc, key) => {
        const product = products[key];
        let type = product.meta_data.find(item => item.key === 'packaging')?.value || 'bottle';
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

    const updateCurrentPackaging =  async (itemIndex, selectedPackagingOption) => {
        
        const newPackages = [...packages];
        const packageToUpdate = newPackages.find(pkg => pkg.index === itemIndex);
        if (packageToUpdate) {
            Object.assign(packageToUpdate, selectedPackagingOption);
        } else {
            console.warn(`Package with index ${itemIndex} not found.`);
            return;
        }

        setPackages(newPackages);
        updateOrderPackagingMeta(newPackages);
    };

    async function updateOrderPackagingMeta(packages) {
        try {
            await apiClient.post(
                window.hges.ajaxUrl,
                {
                    action: 'hges_set_packaging_for_order',
                },
                {
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    packaging: JSON.stringify(packages),
                },
            );
            onChange(packages);
        } catch (error) {
            console.error("Error updating packaging:", error);
        }
    }

    useEffect(() => {
        updateProductsToDispatch();
    }, [packages]);

    const updateProductsToDispatch = () => {
        const updatedProductsNumberByType = { ...initialProductsNumberByType };
        for (const type in updatedProductsNumberByType) {
            if (packages) {
                packages.forEach((pkg) => {
                    if (pkg.containerType === type) {
                        updatedProductsNumberByType[type] -= pkg.itemNumber;
                    }
                });
            }
        }
        setProductsNumberByType(updatedProductsNumberByType);
    };

    const createPackage = () => {
        const newIndex = packages.length > 0 ? packages[packages.length - 1].index + 1 : 1;
        setPackages([...packages, { index: newIndex, itemNumber: 0, width: 0, height: 0, length: 0, weight: { still: 0, sparkling: 0 } }]);
    };

    const removePackage = (index) => {
        setPackages(packages.filter(pkg => pkg.index !== index));
    };


    return (
        <div id="packaging-modal" className={clsx("modal", { 'hidden': !isOpen }) }>
            <div className="modal__content">
                <h3 className="modal__title">{ translate('Package details') }</h3>
                <span className="modal__close" onClick={() => { onClose(false) }}>&times;</span>
                <div className="modal__header">
                    <h4>{ translate('Products to dispatch') }</h4>
                    <div className="products-to-dispatch">
                        {Object.entries(productsNumberByType).map(([type, quantity], index) => (
                            <span key={type}>{quantity} x <strong>{ translate(type) }</strong>{index < Object.entries(productsNumberByType).length - 1 ? ', ' : ''}</span>
                        )) }
                    </div>
                </div>
                <div className="modal__section">
                    <h3>{ translate('Packages') }</h3>
                    <div className="packaging-option-list">
                        {!packages.length ? '' : packages.map((packageItem) => (
                            <PackagingOptionItem key={packageItem.index} packagingOptions={packagingOptions} packageItem={packageItem} onSelect={(selectedOption) => updateCurrentPackaging(packageItem.index, selectedOption) } onRemove={() => removePackage(packageItem.index) } />
                        )) }
                        <div className="plus-round-button" onClick={createPackage}>
                            <span className="dashicons dashicons-plus"></span> <p>{ translate('Add') }</p>
                        </div>
                    </div>
                </div>
                <div className="modal__footer">
                    <button onClick={(e) => e.preventDefault() || onClose(true) }>{ translate('Next') }</button>
                </div>
            </div>
        </div>
    );

};

export default PackagingModal;
