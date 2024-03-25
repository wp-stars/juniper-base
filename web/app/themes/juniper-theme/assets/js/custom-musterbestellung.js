
var selects = document.querySelectorAll('.musterbestellung-custom-fields select');

selects.forEach(function(select) {
    select.addEventListener('change', function() {
        // Get all selected values except the current one (empty value filter for unselected states)
        var selectedValues = Array.from(selects).filter(function(s) { return s.value }).map(function(s) { return s.value; });
        
        const selectedString = JSON.stringify(selectedValues);

        // Calculate expiration date (current date + 7 days)
        const date = new Date();
        date.setTime(date.getTime() + (7 * 24 * 60 * 60 * 1000)); // 7 days in milliseconds
        const expires = "expires=" + date.toUTCString();

        // Set the cookie with the array string and expiration date
        document.cookie = "musterbestellungProducts=" + selectedString + ";" + expires + ";path=/";

        selects.forEach(function(innerSelect) {
            // Iterate over all options in each select
            Array.from(innerSelect.options).forEach(function(option) {
                option.disabled = false; // Enable all options before applying logic

                // Disable option if it's selected in another select and not the current one
                if (selectedValues.includes(option.value) && innerSelect.value !== option.value) {
                    option.disabled = true;
                }
            });
        });
    });
});
const cookieName = 'musterbestellungProducts';

function fetchAndUpdateProductImages() {
    const productIds = JSON.parse(getCookie(cookieName)) || [];
    const container = document.querySelector('#musterbestellung .products');
    const productNumberSpan = document.querySelector('#musterbestellung .product-number');

    fetch(`${musterbestellungParams.restUrl}wps/v1/get-musterbestellung/?ids=${productIds.join(',')}`)
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

                if (product) {
                    // Display product image with tooltip
                    productDiv.innerHTML = `
                        <div class="tooltip group">
                            <img src="${product.image[0]}" style="display: block; margin-bottom: 10px;">
                            <span class="tooltiptext bg-accent rounded-sm p-1 text-black group-hover:visible">${product.name}</span>
                        </div>`;
                } else {
                    // Display placeholder
                    productDiv.innerHTML = `<div class="placeholder" style="width: 50px; height: 50px; background-color: #eee; margin-bottom: 10px;"></div>`;
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

fetchAndUpdateProductImages();

// Set up an observer to watch for cookie changes
let lastCookie = document.cookie;
setInterval(() => {
    if (document.cookie !== lastCookie) {
        lastCookie = document.cookie;
        fetchAndUpdateProductImages();
    }
}, 1000); // Poll every 1000ms