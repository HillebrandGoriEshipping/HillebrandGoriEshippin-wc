const Attachments = ({ attachments, remainingAttachments }) => {
    return (
        <div>
            <h3>{__('Required attachments')}</h3>
            <div>
                <p>
                    {attachments.length}/{requiredAttachments.length}
                </p>
                <ul className="attchments-preview">
                    {attachments.map((attachment) => (
                        <li key={attachment.type}>
                            <span
                                className="marker-green dashicons dashicons-yes-alt"
                                id={`attachments-marker-${attachment.type}`}
                            ></span>
                            {attachment.label}
                            <input
                                type="file"
                                data-file-label={attachment.label}
                                data-file-type={attachment.type}
                                name="fileUpload"
                                className="filepond-file-input"
                                id={`attachments-input-${attachment.type}`}
                            />
                        </li>
                    ))}
                    {remainingAttachments.map((remainingAttachment) => (
                        <li key={remainingAttachment.type}>
                            <span
                                className="marker-red dashicons dashicons-marker"
                                id={`attachments-marker-${remainingAttachment.type}`}
                            ></span>
                            {remainingAttachment.label}
                            <input
                                type="file"
                                data-file-label={remainingAttachment.label}
                                data-file-type={remainingAttachment.type}
                                name="fileUpload"
                                className="filepond-file-input"
                                id={`attachments-input-${remainingAttachment.type}`}
                            />
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
};

export default Attachments;