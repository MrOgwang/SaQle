document.addEventListener("DOMContentLoaded", function () {
	 document.querySelectorAll(".nav-group-header").forEach(header => {

	     header.addEventListener("click", () => {

	         header.classList.toggle("expanded");

	     });

	 });
});

