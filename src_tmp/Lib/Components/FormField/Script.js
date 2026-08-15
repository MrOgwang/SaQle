document.addEventListener("DOMContentLoaded", function () {
	 document.querySelectorAll('.color-input').forEach(control => {
	     const input = control.querySelector('input[type="color"]');
	     const preview = control.querySelector('.color-preview');
	     const hex = control.querySelector('.color-hex');

	     function update() {

	         const color = input.value.toUpperCase();

	         preview.style.backgroundColor = color;
	         hex.textContent = color;
	     }

	     input.addEventListener('input', update);
	     input.addEventListener('change', update);

	     update();
	 });
});