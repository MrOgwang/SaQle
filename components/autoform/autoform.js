document.querySelectorAll('input, textarea, select')
.forEach(field => {

    const toggle = () => {
        if(field.value){
            field.classList.add('filled');
        }else{
            field.classList.remove('filled');
        }
    };

    field.addEventListener('input', toggle);

    toggle();
});

const input = document.getElementById('file-upload-input');
const dropzone = document.getElementById('dropzone');
const fileList = document.getElementById('fileList');

/* =========================
   DRAG STATES
========================= */

['dragenter', 'dragover'].forEach(eventName => {

    dropzone.addEventListener(eventName, e => {

        e.preventDefault();
        dropzone.classList.add('dragging');

    });

});

['dragleave', 'drop'].forEach(eventName => {

    dropzone.addEventListener(eventName, e => {

        e.preventDefault();
        dropzone.classList.remove('dragging');

    });

});

/* =========================
   DROP FILES
========================= */

dropzone.addEventListener('drop', e => {

    input.files = e.dataTransfer.files;

    renderFiles(input.files);

});

/* =========================
   INPUT CHANGE
========================= */

input.addEventListener('change', () => {

    renderFiles(input.files);

});

/* =========================
   RENDER FILES
========================= */

function renderFiles(files){

    fileList.innerHTML = '';

    [...files].forEach((file, index) => {

        const item = document.createElement('div');

        item.className = 'file-upload-file';

        item.innerHTML = `
        
            <div class="file-upload-file-left">

                <div class="file-upload-file-icon">

                    <svg
                        width="20"
                        height="20"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7 7V3h10v4m-5 8v6m0-6l-3 3m3-3l3 3"
                        />
                    </svg>

                </div>

                <div>

                    <div class="file-upload-file-name">
                        ${file.name}
                    </div>

                    <div class="file-upload-file-size">
                        ${formatBytes(file.size)}
                    </div>

                </div>

            </div>

            <button
                class="file-upload-remove"
                data-index="${index}"
            >
                ✕
            </button>
        
        `;

        fileList.appendChild(item);

    });

}

/* =========================
   FILE SIZE FORMAT
========================= */

function formatBytes(bytes){

    if(bytes === 0) return '0 Bytes';

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