console.log('disabling add to cart')
var addCartButtons = document.querySelectorAll('.single_add_to_cart_button');

addCartButtons.forEach(function(button) {
    button.disabled = true

    if (button.classList.contains('disabled')) {
        button.disabled = true; // Disable button if it's not already disabled by WooCommerce
        var productForm = button.closest('form.cart');
        console.log(productForm)
        if (productForm) {
            var productTypeInput = productForm.querySelector('input[name="product-type"]');
            if (productTypeInput && productTypeInput.value === 'musterbestellung') {
                button.disabled = true; // Specifically disable for "Musterbestellung" types
                button.value = 'Musterbestellung Product Already in Cart'; // Optional: Change button text
            }
        }
    }
});