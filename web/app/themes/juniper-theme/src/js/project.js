
const toggleNavbar = (event) => {
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        document.querySelector(".navbar").classList.add("navbar-scrolled")
    } else {
        document.querySelector(".navbar").classList.remove("navbar-scrolled")
    }
} 

document.addEventListener("scroll", toggleNavbar)


const toggleMenu = () => {
    document.querySelector(".navbar").classList.toggle("navbar-open")
}

let navbarToggler = document.querySelector('#navbarToggler')
navbarToggler.addEventListener('click', () => {
    toggleMenu()
})


// open and close the dialog modal function
const openModal = (id) => {
    const dialog = document.getElementById("modal-" + id);
    dialog.showModal();
}

const closeModal = (id) => {
    const dialog = document.getElementById("modal-" + id);
    dialog.close();
}