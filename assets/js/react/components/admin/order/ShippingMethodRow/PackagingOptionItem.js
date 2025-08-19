const { __ } = wp.i18n;

const PackagingOptionItem = ({ packageItem, packagingOptions, onSelect, onRemove }) => {
    const onSelectChange = (e) => {
        onSelect(packagingOptions.find(option => option.id === Number(e.target.value)) );
    };
console.log('packageItem', packageItem);
    return (
        <div className="packaging-option-item">
            <h4>Package {packageItem.index}</h4>
            <div className="remove-button" onClick={() => onRemove(packageItem)}>
                <span className="dashicons dashicons-no-alt"></span>
            </div>
            <div>
                <label>{__('Type')}:</label> 
                <select 
                    value={packageItem.id} 
                    onChange={onSelectChange}
                >
                    {packagingOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.itemNumber}x{option.containerType} ({option.width}x{option.height}x{option.length} cm)
                        </option>
                    ))}
                </select>
            </div>
        </div>
    );
};

export default PackagingOptionItem;
