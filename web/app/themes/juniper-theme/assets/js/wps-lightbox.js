
// instantiate lightboxes
console.log(document.querySelectorAll('.wp-block-image'))
var lightbox = GLightbox({
    selector: '.wp-block-image a'
});

console.log(lightbox)
