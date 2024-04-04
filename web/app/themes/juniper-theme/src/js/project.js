
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

jQuery(document).ready(function() {
    console.log("ready");

    // Check and initialize slider for .product-card-slider
    if (jQuery('.product-card-slider').length > 0) {
        jQuery('.product-card-slider').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: true,
            dots: true,
            // asNavFor: '.woocommerce-product-gallery-thumbnails'
        });
    } else {
        console.log('Product card slider element not found');
    }

    jQuery('.product-card-slider').on('click', '.slick-dots li', function(e) {
        e.preventDefault();
        e.stopPropagation(); // To stop the event from bubbling up to the parent link
    });

    // Check and initialize slider for .woocommerce-product-gallery__wrapper
    if (jQuery('.woocommerce-product-gallery__wrapper').length > 0) {
        jQuery('.woocommerce-product-gallery__wrapper').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            fade: true,
            dots: true,
            // asNavFor: '.woocommerce-product-gallery-thumbnails'
        });
    } else {
        console.log('WooCommerce product gallery wrapper element not found');
    }
});

