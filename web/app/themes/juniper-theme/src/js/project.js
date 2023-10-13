
const toggleNavbar = (event) => {
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        document.querySelector(".navbar").classList.add("bg-dark", "bg-opacity-80")
    } else {
        document.querySelector(".navbar").classList.remove("bg-dark", "bg-opacity-80")
    }
} 

document.addEventListener("scroll", toggleNavbar)


const toggleMenu = () => {
    document.querySelector(".navbar").classList.toggle("bg-dark")
    document.querySelector(".navbar").classList.toggle("bg-opacity-80")
}

let navbarToggler = document.querySelector('#navbarToggler')
navbarToggler.addEventListener('click', () => {
    toggleMenu()
}) 
