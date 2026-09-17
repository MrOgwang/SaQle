const password = document.getElementById('password');
const toggle = document.getElementById('toggle-password');

toggle.addEventListener('click', () => {
     const visible = password.type === 'text';

     password.type = visible ? 'password' : 'text';
     toggle.textContent = visible ? 'SHOW' : 'HIDE';
     toggle.setAttribute(
         'aria-label',
         visible ? 'Show password' : 'Hide password'
     );
});