document.addEventListener("DOMContentLoaded", function () {

	 lucide.createIcons();

	 document.querySelectorAll(".nav-group-header").forEach(header => {

	     header.addEventListener("click", () => {

	         header.classList.toggle("expanded");

	     });

	 });
});