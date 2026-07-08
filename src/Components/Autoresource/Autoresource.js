(() => {
     document.querySelectorAll('.file-upload').forEach(initFileUpload);
     function initFileUpload(root) {
         const input = root.querySelector('.file-upload-input');
         const dropzone = root.querySelector('.file-upload-dropzone');
         const fileList = root.querySelector('.file-upload-files');
         const button = root.querySelector('.file-upload-button');

         let filesState = [];

         /*upload button click*/
         button.addEventListener('click', (e) => {
             e.preventDefault();
             e.stopPropagation();
             input.click();
         });

         /*drag states*/
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

         /*drop files*/
         dropzone.addEventListener('drop', e => {
             e.preventDefault();
             const droppedFiles = Array.from(e.dataTransfer.files);
             updateFiles([...filesState, ...droppedFiles]);
         });

         /*input change*/
         input.addEventListener('change', () => {
             const selectedFiles = Array.from(input.files);
             updateFiles(selectedFiles);
         });

         /*remove file*/
         fileList.addEventListener('click', e => {
             const btn = e.target.closest('.file-upload-remove');
             if (!btn) return;

             const index = Number(btn.dataset.index);
             filesState.splice(index, 1);

             syncInput();
             renderFiles();
         });

         /*update state*/
         function updateFiles(newFiles){
             filesState = newFiles;
             syncInput();
             renderFiles();
         }

         function syncInput(){
             const dt = new DataTransfer();
             filesState.forEach(file => dt.items.add(file));
             input.files = dt.files;
         }

         /*render files*/
         function renderFiles(){
             fileList.innerHTML = '';

             filesState.forEach((file, index) => {
                 const item = document.createElement('div');
                 item.className = 'file-upload-file';
                 item.innerHTML = `
                     <div class="file-upload-file-left">
                         <div class="file-upload-file-icon">
                            📄
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
                     <button type="button"class="file-upload-remove" data-index="${index}">✕</button>
                 `;
                 fileList.appendChild(item);
             });
         }

         /*file size format*/
         function formatBytes(bytes) {
             if (bytes === 0) return '0 Bytes';

             const k = 1024;
             const sizes = ['Bytes', 'KB', 'MB', 'GB'];
             const i = Math.floor(Math.log(bytes) / Math.log(k));
             return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
         }
     }

     document.querySelectorAll('input, textarea, select').forEach(field => {
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

})();