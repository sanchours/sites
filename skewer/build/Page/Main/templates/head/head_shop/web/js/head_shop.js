$(function() {

    $('.js-sidebar-catalog-show').on('click', function(event) {
        event.preventDefault();
        showSidebar();
    });
    $('.js-sidebar-catalog-hide').on('click', function(event) {
        event.preventDefault();
        hideSidebar();
    });

    function showSidebar() {
        $('.js-sidebar-catalog').addClass('l-sidebar--open');
        $('.js-sidebar-catalog-block').addClass('l-sidebar-block--open');

        $('html, body').css('overflow', 'hidden');

    }

    function hideSidebar() {
        $('.js-sidebar-catalog').removeClass('l-sidebar--open');
        $('.js-sidebar-catalog-block').removeClass('l-sidebar-block--open');

        $('html, body').css('overflow', 'visible');
    }
});//ready