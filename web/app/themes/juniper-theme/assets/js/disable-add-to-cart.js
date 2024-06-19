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

window.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.wc-block-cart-item__remove-link').forEach(function(button){
        const ariaLabel = button.getAttributeNode('aria-label').value;
        if(ariaLabel == "Farbmuster aus Warenkorb entfernen" || ariaLabel == "Remove Farbmuster from cart" || ariaLabel == "Remove Color samples from cart"){
            button.remove();
        }
    });
}, false);