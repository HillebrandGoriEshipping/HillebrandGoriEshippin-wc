const { __ } = wp.i18n;

const PackagingOptionItem = ({ packageItem, packagingOptions, onSelect }) => {
    console.log("PackagingOptionItem props:", { packageItem, packagingOptions });
    return (
        <div className="packaging-option-item" onClick={() => onSelect(packageItem)}>
            <h4>Package {packageItem.id}</h4>
            <div>
                <label>{__('Type')}:</label> 
                <select value={packageItem.type} onChange={(e) => packageItem.type = e.target.value}>
                    {packagingOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                            {option.itemNumber}x{option.containerType} ({option.width}x{option.height}x{option.length} cm)
                        </option>
                    ))}
                </select>
            </div>
            <div>
                <h5>{__('Weight')}:</h5>
                <ul>
                    <li>{__('Still:')} {packageItem.weight.still} kg</li>
                    <li>{__('Sparkling:')} {packageItem.weight.sparkling} kg</li>
                </ul>
            </div>
        </div>
    );
};

export default PackagingOptionItem;
