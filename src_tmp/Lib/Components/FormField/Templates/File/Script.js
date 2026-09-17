(function(){

    document.querySelectorAll('.file-upload').forEach(initFileUpload);

    function initFileUpload(root) {

        const input = root.querySelector('.file-upload-input');
        const dropzone = root.querySelector('.file-upload-dropzone');
        const fileList = root.querySelector('.file-upload-files');
        const button = root.querySelector('.file-upload-button');

        const multiple = root.dataset.multiple === 'true';

        const maxFiles = root.dataset.maxFiles
            ? Number(root.dataset.maxFiles)
            : null;

        let filesState = [];

        /* upload button click */
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            /*
             * Don't open the file picker when the maximum
             * number of files has already been reached.
             */
            if (maxFiles && filesState.length >= maxFiles) {
                return;
            }

            input.click();
        });


        /* drag states */
        ['dragenter', 'dragover'].forEach(eventName => {

            dropzone.addEventListener(eventName, e => {
                e.preventDefault();

                if (maxFiles && filesState.length >= maxFiles) {
                    return;
                }

                dropzone.classList.add('dragging');
            });

        });


        ['dragleave', 'drop'].forEach(eventName => {

            dropzone.addEventListener(eventName, e => {
                e.preventDefault();
                dropzone.classList.remove('dragging');
            });

        });


        /* drop files */
        dropzone.addEventListener('drop', e => {

            e.preventDefault();

            const droppedFiles = Array.from(e.dataTransfer.files);

            addFiles(droppedFiles);
        });


        /* input change */
        input.addEventListener('change', () => {

            const selectedFiles = Array.from(input.files);

            addFiles(selectedFiles);
        });


        /* remove file */
        fileList.addEventListener('click', e => {

            const btn = e.target.closest('.file-upload-remove');

            if (!btn) {
                return;
            }

            const index = Number(btn.dataset.index);

            filesState.splice(index, 1);

            syncInput();
            renderFiles();
            updateControlState();
        });


        /*
         * Add files to the current state while respecting
         * the multiple/max-files configuration.
         */
        function addFiles(newFiles) {

            if (!multiple) {
                filesState = newFiles.slice(0, 1);
            }
            else {

                const combined = [
                    ...filesState,
                    ...newFiles
                ];

                if (maxFiles) {
                    filesState = combined.slice(0, maxFiles);
                }
                else {
                    filesState = combined;
                }
            }

            syncInput();
            renderFiles();
            updateControlState();
        }


        /* synchronize actual input */
        function syncInput() {

            const dt = new DataTransfer();

            filesState.forEach(file => {
                dt.items.add(file);
            });

            input.files = dt.files;
        }


        /* render files */
        function renderFiles() {

            fileList.innerHTML = '';

            filesState.forEach((file, index) => {

                const item = document.createElement('div');

                item.className = 'file-upload-file';

                const left = document.createElement('div');
                left.className = 'file-upload-file-left';

                const icon = document.createElement('div');
                icon.className = 'file-upload-file-icon';

                /*
                 * Images get a real preview.
                 * Everything else gets the generic icon.
                 */
                if (file.type.startsWith('image/')) {

                    const img = document.createElement('img');

                    img.className = 'file-upload-file-preview';
                    img.alt = file.name;

                    const url = URL.createObjectURL(file);

                    img.src = url;

                    /*
                     * Release the object URL once the image
                     * has finished loading.
                     */
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                    };

                    icon.appendChild(img);

                }
                else {

                    icon.textContent = '📄';
                }


                const details = document.createElement('div');

                const name = document.createElement('div');
                name.className = 'file-upload-file-name';
                name.textContent = file.name;

                const size = document.createElement('div');
                size.className = 'file-upload-file-size';
                size.textContent = formatBytes(file.size);

                details.appendChild(name);
                details.appendChild(size);

                left.appendChild(icon);
                left.appendChild(details);


                const remove = document.createElement('button');

                remove.type = 'button';
                remove.className = 'file-upload-remove';
                remove.dataset.index = index;
                remove.textContent = '✕';


                item.appendChild(left);
                item.appendChild(remove);

                fileList.appendChild(item);
            });
        }


        /*
         * Disable the choose button when the maximum
         * number of files has been reached.
         */
        function updateControlState() {

            const limitReached =
                maxFiles && filesState.length >= maxFiles;

            button.disabled = limitReached;

            if (limitReached) {
                root.classList.add('file-upload-limit-reached');
            }
            else {
                root.classList.remove('file-upload-limit-reached');
            }
        }


        /* file size format */
        function formatBytes(bytes) {

            if (bytes === 0) {
                return '0 Bytes';
            }

            const k = 1024;

            const sizes = [
                'Bytes',
                'KB',
                'MB',
                'GB'
            ];

            const i = Math.floor(
                Math.log(bytes) / Math.log(k)
            );

            return parseFloat(
                (bytes / Math.pow(k, i)).toFixed(2)
            ) + ' ' + sizes[i];
        }

    }

})();