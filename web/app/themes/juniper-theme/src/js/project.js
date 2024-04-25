
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
    const initSlider = ($element) => {
        // Initialize the slider first with default settings
        $element.each(function() {
            const $slider = jQuery(this);
            if (!$slider.hasClass('slick-initialized')) {
                $slider.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    fade: true,
                    dots: false // Initialize without dots
                });

                // Check the number of slides after initialization
                const slideCount = $slider.slick('getSlick').slideCount;
                if (slideCount > 1) {
                    // Update the slider options to show dots if there are more than one slide
                    $slider.slick('slickSetOption', 'dots', true, true);
                }
            }
        });
    };

    // Initialize slider on page load
    initSlider(jQuery('.product-card-slider'));

    // Event delegation for dynamically added elements
    jQuery(document).on('click', '.product-card-slider .slick-dots li', function(e) {
        e.preventDefault();
        e.stopPropagation(); // To stop the event from bubbling up to the parent link
    });

    // Check and initialize slider for .woocommerce-product-gallery__wrapper
    if (jQuery('.woocommerce-product-gallery__wrapper').length > 0) {
        initSlider(jQuery('.woocommerce-product-gallery__wrapper'));
    } else {
        console.log('WooCommerce product gallery wrapper element not found');
    }

    // Event listener for filter rendering done
    jQuery(document).on('filterRenderingDone', function () {
        initSlider(jQuery('.product-card-slider')); // Reinitialize the slider after filter rendering is done
    });
});
