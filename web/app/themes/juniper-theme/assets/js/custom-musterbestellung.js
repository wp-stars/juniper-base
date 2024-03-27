const cookieName = 'musterbestellungProducts';

function fetchAndUpdateMusterbestellung() {
    const productIds = JSON.parse(getCookie(cookieName)) || [];
    const container = document.querySelector('#musterbestellung .products');
    const productNumberSpan = document.querySelector('#musterbestellung .product-number');

    fetch(`${customMusterbestellungParams.restUrl}wps/v1/get-musterbestellung/?ids=${productIds.join(',')}`)
        .then(response => response.json())
        .then(products => {
            // Update the product number display
            productNumberSpan.textContent = `${products.length}/3`;

            // Clear existing products
            container.innerHTML = '';

            // Fill up to 3 product slots
            for (let i = 0; i < 3; i++) {
                const product = products[i];
                const productDiv = document.createElement('div');
                productDiv.className = 'product-slot';

                if (product && product.image) {
                    // Display product image with tooltip
                    productDiv.innerHTML = `
                        <div class="tooltip group mb-4">
                            <div class="product overflow-hidden rounded-full">
                                <img src="${product.image[0]}" style="display: block;">
                            </div>
                            <span class="tooltiptext bg-accent rounded-sm p-1 text-black invisible group-hover:visible">${product.name}</span>
                        </div>`;
                } else {
                    // Display placeholder
                    productDiv.innerHTML = `<div class="placeholder mb-4 border-solid border-2 border-black rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path d="M19.25 11C19.25 11.1823 19.1776 11.3572 19.0486 11.4861C18.9197 11.6151 18.7448 11.6875 18.5625 11.6875H11.6875V18.5625C11.6875 18.7448 11.6151 18.9197 11.4861 19.0486C11.3572 19.1776 11.1823 19.25 11 19.25C10.8177 19.25 10.6428 19.1776 10.5139 19.0486C10.3849 18.9197 10.3125 18.7448 10.3125 18.5625V11.6875H3.4375C3.25516 11.6875 3.0803 11.6151 2.95136 11.4861C2.82243 11.3572 2.75 11.1823 2.75 11C2.75 10.8177 2.82243 10.6428 2.95136 10.5139C3.0803 10.3849 3.25516 10.3125 3.4375 10.3125H10.3125V3.4375C10.3125 3.25516 10.3849 3.0803 10.5139 2.95136C10.6428 2.82243 10.8177 2.75 11 2.75C11.1823 2.75 11.3572 2.82243 11.4861 2.95136C11.6151 3.0803 11.6875 3.25516 11.6875 3.4375V10.3125H18.5625C18.7448 10.3125 18.9197 10.3849 19.0486 10.5139C19.1776 10.6428 19.25 10.8177 19.25 11Z" fill="black"/>
                        </svg>
                    </div>`;
                }
                container.appendChild(productDiv);
            }
        });
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

fetchAndUpdateMusterbestellung();

// Set up an observer to watch for cookie changes
let lastCookie = document.cookie;
setInterval(() => {
    if (document.cookie !== lastCookie) {
        lastCookie = document.cookie;
        fetchAndUpdateMusterbestellung();
    }
}, 1000); // Poll every 1000ms