(function(){

     var formOptionsEndpoint = "/forms/options";
     var formCascadeOptionsEndpoint = "/forms/options/cascade";
     var formSearchOptionsEndpoint = "/forms/options/search";
     var csrfToken = $("#csrf_token").val();

     function debounce(callback, delay = 300){
         let timeout;

         return (...args) => {
             clearTimeout(timeout);

             timeout = setTimeout(() => {
                 callback(...args);
             }, delay);
         };
     }

     var getFormOptions = async (formName, model, field, cfields = null, selected = null) => {
         const params = new URLSearchParams({
             model:   model,
             fname:   formName,
             field:   field,
             cfields: cfields,
             val:     selected
         });

         try{

             let endPoint = cfields && selected ? formCascadeOptionsEndpoint : formOptionsEndpoint;

             const response = await fetch(endPoint + "?" + params, {
                 headers: {
                     "Accept": "application/json"
                 }
             });

             if(!response.ok){
                 throw new Error(`Failed to load ${endPoint}`);
             }

             return await response.json();

         }catch(error){
             console.error(error);
         }
     };

     var searchFormOptions = async (formName, model, field, keyword, signal) => {
         const params = new URLSearchParams({
             model:   model,
             fname:   formName,
             field:   field,
             keyword: keyword
         });

         try{

             let endPoint = formSearchOptionsEndpoint;

             const response = await fetch(endPoint + "?" + params, {
                 signal: signal,
                 headers: {
                     "Accept": "application/json"
                 }
             });

             if(!response.ok){
                 throw new Error(`Failed to load ${endPoint}`);
             }

             return await response.json();

         }catch(error){
             /*gnore aborted requests*/
             if(error.name === "AbortError"){
                 return null;
             }

             console.error(error);
         }
     };

     document.addEventListener("DOMContentLoaded", async () => {

         const selects = document.querySelectorAll("form select.auto_form_select.opts_deferred");

         for(const select of selects){

             const formName = select.dataset.fname;
             const model = select.dataset.model;
             const field = select.dataset.field;

             let result = await getFormOptions(formName, model, field);

             select.innerHTML = "";

             select.appendChild(new Option("-- Select --", ""));

             for(const id in result.data){
                 select.appendChild(
                     new Option(result.data[id], id)
                 );
             }
         }
     });

     document.querySelectorAll('.opts_cascade').forEach(selectBox => {
         selectBox.addEventListener('change', async (e) => {
             const formName = selectBox.dataset.fname;
             const model = selectBox.dataset.model;
             const field = selectBox.dataset.field;
             const cfields = selectBox.dataset.cfields;
             const selected = e.target.value;

             let result = await getFormOptions(formName, model, field, cfields, selected);

             for(const field_name in result.data){
                 
                 const targetSelectBox = document.getElementById(field_name);
                 let options = result.data[field_name];

                 targetSelectBox.innerHTML = "";

                 targetSelectBox.appendChild(new Option("-- Select --", ""));

                 for(const id in options){
                     targetSelectBox.appendChild(
                         new Option(options[id], id)
                     );
                 }
             }
         });
     });

     document.querySelectorAll(".search-select").forEach(initSearchSelect);

     function initSearchSelect(container){

         const multiple = container.dataset.multiple;
         const chipsContainer = container.querySelector(".chips-container");
         const hiddenInputs = container.querySelector(".search-hidden-inputs");
         
         const mainInputId = container.dataset.field;
         const searchInput = container.querySelector(".search-select-input");
         const mainInput = container.querySelector("#" + mainInputId);
         const results = container.querySelector(".search-select-results");

         let controller = null;

         searchInput.addEventListener("input", debounce(async () => {

             if(mainInput){
                 mainInput.value = "";
             }

             const term = searchInput.value.trim().toLowerCase();

             if(term.length === 0){
                 results.classList.remove("show");
                 return;
             }

             const formName = searchInput.dataset.fname;
             const model = searchInput.dataset.model;
             const field = searchInput.dataset.field;

             /*cancel the previous request if it's still running*/
             if(controller){
                 controller.abort();
             }

             /*reate a new controller for this request*/
             controller = new AbortController();

             let result = await searchFormOptions(formName, model, field, term, controller.signal);

             controller = null;

             /*the request was aborted*/
             if(result === null){
                 return;
             }

             const matches = [];

             for(const id in result.data){
                 matches.push({
                     id: id,
                     name: result.data[id]
                 });
             }

             renderSearchResults(results, searchInput, matches, mainInput, multiple, chipsContainer, hiddenInputs);
         }, 300));

         document.addEventListener("click", e => {
             if(!container.contains(e.target)){
                 results.classList.remove("show");
             }
         });
     }

     function renderSearchResults(results, searchInput, items, mainInput, multiple, chipsContainer, hiddenInputs){
         results.innerHTML = "";

         if(items.length === 0){

             results.innerHTML = '<div class="search-empty">No matches</div>';

             results.classList.add("show");
             return;
         }

         items.forEach(item => {
             const option = document.createElement("div");

             option.className = "search-option";
             option.textContent = item.name;

             option.onclick = () => {
                 if(multiple){
                     addChip(item, chipsContainer, hiddenInputs, searchInput);
                 }else{
                     mainInput.value = item.id;
                     searchInput.value = item.name;
                     searchInput.dataset.value = item.id;
                 }

                 results.classList.remove("show");
             };

             results.appendChild(option);
         });

         results.classList.add("show");
     }

     function addChip(item, chipsContainer, hiddenInputs, searchInput){

         /*prevent duplicate selections*/
         if(hiddenInputs.querySelector(`input[value="${item.id}"]`))
             return;

         const chip = document.createElement("div");
         chip.className = "chip";

         const label = document.createElement("span");
         label.textContent = item.name;

         const remove = document.createElement("span");
         remove.className = "chip-remove";
         remove.innerHTML = "&times;";

         chip.append(label, remove);

         chipsContainer.appendChild(chip);

         const hidden = document.createElement("input");
         hidden.type = "hidden";
         hidden.name = searchInput.dataset.field + "[]";
         hidden.value = item.id;

         hiddenInputs.appendChild(hidden);

         remove.onclick = () => {
             chip.remove();
             hidden.remove();
         };

         searchInput.value = "";
         searchInput.focus();
     }

})();