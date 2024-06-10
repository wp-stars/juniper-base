// noinspection JSUnresolvedReference

AOS.init();

jQuery(document).on('filterRefreshRenderedElements', function() {
    AOS.refresh();
})