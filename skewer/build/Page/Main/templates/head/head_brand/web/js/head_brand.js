$(function() {

    var scrollTopPos = $(window).scrollTop();

    function showFixedHead() {
        var $fixedBlock = $('.js-fixedhead');
        var breakPoint = ( $('.b-picture').length ) ? $('.b-picture').offset().top + $('.b-picture').outerHeight(true) : $('.b-picture2').offset().top + $('.b-picture2').outerHeight(true);

        scrollTopPos = $(window).scrollTop();

        if ( scrollTopPos > breakPoint ) {
            $fixedBlock.addClass('b-fixed-head--on');
        } else {
            $fixedBlock.removeClass('b-fixed-head--on');
        }

    }
    showFixedHead()

    $(window).on('scroll resize', function() {
        showFixedHead()
    })

});//ready