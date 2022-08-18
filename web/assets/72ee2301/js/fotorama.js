$(function () {

    if ($('.js-catalog-detail-fotorama').length){


        $('.js-catalog-detail-fotorama').fotorama(window.skewerConfigs.oFotoramaConfig);

        var galWidth = $('.js-catalog-detail-fotorama-wrap').outerWidth(true);
        var galHeight = $('.js-catalog-detail-fotorama-wrap').outerHeight(true);

        $(window).on('resize fotorama:ready', function() {
            galWidth = $('.js-catalog-detail-fotorama-wrap').outerWidth(true);
            galHeight = $('.js-catalog-detail-fotorama-wrap').outerHeight(true);
        })

        $('.js-catalog-detail-fotorama').on({
            'fotorama:fullscreenenter': function() {
                $('.js-catalog-detail-fotorama-wrap').css({'width': galWidth, 'height': galHeight});
            },
            'fotorama:fullscreenexit': function() {
                $('.js-catalog-detail-fotorama-wrap').css({'width': 'auto', 'height': 'auto'});
            }
        })
    }

});