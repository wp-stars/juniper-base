
const toggleNavbar = (event) => {
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        document.querySelector(".navbar").classList.add("bg-dark", "bg-opacity-80")
    } else {
        document.querySelector(".navbar").classList.remove("bg-dark", "bg-opacity-80")
    }
} 

document.addEventListener("scroll", toggleNavbar)
