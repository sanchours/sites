$(function() {

    $('.js-sidebar-show').on('click', function(event) {
        event.preventDefault();
        showSidebar();
    });
    $('.js-sidebar-hide').on('click', function(event) {
        event.preventDefault();
        hideSidebar();
    });

    function showSidebar() {

        $('.js-sidebar').addClass('l-sidebar--open');
        $('.js-sidebar-block').addClass('l-sidebar-block--open');

        var tempWidth,
            tempWidthNew,
            scrollWidth;

        // scroll width compensation
        tempWidth = $('body').outerWidth(true);
        $('body').addClass('g-ov-h');
        tempWidthNew = $('body').outerWidth(true);
        scrollWidth = tempWidthNew - tempWidth;
        $('body').css({ 'padding-right' : scrollWidth } );
        $('.b-fixed-head').css({ 'right' : scrollWidth })

    }

    function hideSidebar() {
        $('.js-sidebar').removeClass('l-sidebar--open');
        $('.js-sidebar-block').removeClass('l-sidebar-block--open');

        // scroll width compensation
        $('body').removeClass('g-ov-h');
        $('body').css({ 'padding-right' : 0 });
        $('.b-fixed-head').css({ 'right' : 0 })
    }

    // меню в сайдбаре
    $('.js-sidebarmenu-achor').on('click', function(event) {
        event.preventDefault();
        var $menuItem = $(this).parent('.js-sidebarmenu-achor-wrap').parent('.js-sidebarmenu-item');
        var $menuNextLvl = $(this).parents('.js-sidebarmenu-achor-wrap').next('.js-sidebarmenu-content');

        $menuItem.toggleClass('sidebar-menu__item--open');
        $menuNextLvl.stop(true, false).slideToggle('slow')
    });
});
