const handleMusterbestellungSelects = () => {
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
}

let selects = document.querySelectorAll('.musterbestellung-custom-fields select');

selects.forEach(function(select) {
    select.addEventListener('change', function() {
        handleMusterbestellungSelects()
    });
});

handleMusterbestellungSelects()

document.addEventListener('change', function(event) {
    if (event.target && event.target.classList.contains('qty')) {
        var maxVal = event.target.getAttribute('max');
        console.log(maxVal)
        if (maxVal === '1') {
            console.log('found max val')
            event.target.value = Math.min(event.target.value, maxVal);
        }
    }
}, true);

