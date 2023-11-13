const menuToggler = document.getElementById('navbarToggler');
menuToggler.addEventListener('click', (e) => {
	e.currentTarget.classList.toggle('navbar-open')
})
