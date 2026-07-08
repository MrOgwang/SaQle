(function () {

     document.querySelectorAll('.sort-but').forEach(sortButton => {
         sortButton.addEventListener('click', e => {
             const column = Number(sortButton.dataset.column);
             const order = sortButton.dataset.order;
             const table = sortButton.dataset.table;

             const tbody = document.querySelector('#' + table + ' tbody');
             const rows = Array.from(tbody.rows);

             rows.sort((a, b) => {
                 const av = a.cells[column].textContent.trim();
                 const bv = b.cells[column].textContent.trim();

                 const result = av.localeCompare(bv, undefined, {
                     numeric: true,
                     sensitivity: 'base'
                 });

                 return order === 'asc' ? result : -result;
             });

             tbody.replaceChildren(...rows);

             sortButton.dataset.order = order === 'asc' ? 'desc' : 'asc';
         });
     });

     const searchInput = document.getElementById('searchInput');

     searchInput.addEventListener('keydown', (e) => {
         if (e.key !== 'Enter') return;

         const value = searchInput.value.trim();
         const url = new URL(window.location.href);

         if(value){
             url.searchParams.set('search', value);
         }else{
             url.searchParams.delete('search');
         }

         window.location.href = url.toString();
     });

})();