const { translate } = window.hges.i18n;
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginFileMetadata from 'filepond-plugin-file-metadata';
import apiClient from '../../../../../apiClient';

registerPlugin(FilePondPluginFileMetadata);

const Attachments = ({ attachments, requiredAttachments, remainingAttachments }) => {
    let currentAttachments = attachments;
    const serverConfig = {
        process: async (fieldName, file, metadata, load, error, progress, abort) => {
            const response = await apiClient.upload(window.hges.apiUrl + '/v2/attachments/upload', {}, {file, type: metadata.fileType});
            if (response.error) {
                error(response.error);
            }
            if (response.progress) {
                progress(response.progress);
            } else {
                load(response.id);

            }
            return {
                abort: () => {
                    request.abort();
                    abort();
                },
            };
        },
    };

    const onProcessFile = (error, file) => {
        if (error) {
            fileUploadedError(error, file);
        } else {
            fileUploadedSuccess(file);
        }
    };

    const fileUploadedError = (error, file) => {
            console.error('File upload error:', error, file);
    }
    
    const  fileUploadedSuccess = async (file) => {
    
        if (currentAttachments.some(attachment => attachment.type === file.getMetadata('fileType'))) {
            currentAttachments = currentAttachments.filter(attachment => attachment.type !== file.getMetadata('fileType'));
        }

        currentAttachments.push({
            id: file.serverId,
            name: file.filename,
            url: file.serverUrl,
            mimeType: file.fileType,
            type: file.getMetadata('fileType'),
            label: file.getMetadata('label')
        });

        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.remove('marker-red');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.remove('dashicons-marker');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.add('marker-green');
        document.querySelector('#attachments-marker-' + file.getMetadata('fileType')).classList.add('dashicons-yes-alt');

        try {
            const response = await apiClient.post(
                window.hges.ajaxUrl, 
                {
                    action: 'hges_update_order_attachments',
                },
                {
                    orderId: new URLSearchParams(window.location.search).get('id'),
                    attachments: currentAttachments,
                },
            );
        } catch (error) {
            console.error('Attachments update failed', error);
        }
    }

    return (
        <div>
            <h3>{ translate('Required attachments') }</h3>
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
                            <FilePond name="fileUpload" allowMultiple={false} server={serverConfig} fileMetadataObject={{ fileType: attachment.type, label: attachment.label }} onprocessfile={onProcessFile} credits={false} />
                        </li>
                    )) }
                    {remainingAttachments.map((remainingAttachment) => (
                        <li key={remainingAttachment.type}>
                            <span
                                className="marker-red dashicons dashicons-marker"
                                id={`attachments-marker-${remainingAttachment.type}`}
                            ></span>
                            {remainingAttachment.label}
                            <FilePond name="fileUpload" allowMultiple={false} server={serverConfig} fileMetadataObject={{ fileType: remainingAttachment.type, label: remainingAttachment.label }} onprocessfile={onProcessFile} credits={false} />

                        </li>
                    )) }
                </ul>
            </div>
        </div>
    );
};

export default Attachments;