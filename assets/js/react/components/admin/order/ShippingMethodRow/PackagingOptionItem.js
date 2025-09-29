const { __ } = wp.i18n;

const PackagingOptionItem = ({ packageItem, packagingOptions, onSelect, onRemove }) => {
    const onSelectChange = (e) => {
        onSelect(packagingOptions.find(option => option.id === Number(e.target.value)) );
    };
    
    return (
        <div className="packaging-option-item">
            <h4>Package {packageItem.index}</h4>
            <div className="remove-button" onClick={() => onRemove(packageItem)}>
                <span className="dashicons dashicons-no-alt"></span>
            </div>
            <div className="package-select">
                <label>{__('Type')}</label>
                <select
                    value={packageItem.id}
                    onChange={onSelectChange}
                >
                    {packagingOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.itemNumber} x {option.containerType}
                        </option>
                    ))}
                </select>
            </div>
            <div className="package-modal-details">
                <p>{__('Selected')}: </p>
                <p>{packageItem.width} x {packageItem.height} x {packageItem.length} cm</p>
            </div>
        </div>
    );
};

export default PackagingOptionItem;
