const menuToggler = document.getElementById('navbarToggler');
menuToggler.addEventListener('click', (e) => {
	e.currentTarget.classList.toggle('navbar-open')
})


document.addEventListener('DOMContentLoaded', function () {
    var dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (event) {
			event.preventDefault(); 
            var expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);

            var menu = this.closest('li').querySelector('.dropdown-menu');
            if (menu) {
                menu.style.display = expanded ? 'none' : 'block';
            }
        });
    });
});
