// set hidden field value
let productIdField = document.querySelector(".product-inquiry-name-input")
if(productIdField) {
    productIdField.value = wpVars.postName
    // Get the modal
    let modal = document.getElementById("productModal");
    let btn = document.getElementById("productModalBtn");
    let span = document.querySelector("#productModal .close");

    // When the user clicks on the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}

let wishlistBtn = document.querySelector('#add-to-sample-wishlist')
if(wishlistBtn) {
    wishlistBtn.addEventListener('click', () => {
        let wishlistLink = document.querySelector('#view-wishlist')
        wishlistLink.classList.remove('hide-wishlist-link')
        wishlistLink.classList.add('show-wishlist-link')
    })
}


let newsletterBtns = document.querySelectorAll('.newsletterBtn')
if(newsletterBtns.length) {
    newsletterBtns.forEach(btn => {
        let container = btn.closest(".newsletter")
        // Get the modal
        let modal = container.querySelector(".newsletterModal")
        let span = container.querySelector(".newsletterModal .close")

        btn.onclick = () => {
            modal.style.display = "block"
        }

        span.onclick = function() {
            modal.style.display = "none"
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none"
            }
        }
    })
}


