$(document).ready(function () {

    $(document).on('click','.js-quick-view', function(){

        var params = {};

        params.moduleName = 'CatalogViewer';
        params.cmd = 'QuickView';
        params.item = $( this ).data( 'id' );
        params.sectionId = $( this ).data( 'sectionid' );
        params.language = $('#current_language').val();

        $.post('/ajax/ajax.php', params, function(jsonData) {

            var aData = jQuery.parseJSON(jsonData);

            $.fancybox.open(aData.html,{
                dataType : 'html',
                autoSize: false,
                autoCenter: true,
                width: '940px',
                arrows: false,
                height: 'auto',
                openEffect: 'none',
                closeEffect: 'none',
                afterShow: function(){

                    var oRating = new Rating('js-rating',true);
                    oRating.initRatings();

                    $('.js-catalog-detail-fotorama')
                        .on('fotorama:ready', function(e, fotorama, extra){
                            // Убираем фокус со стрелок фоторамы
                            $('.fotorama__arr.fotorama__arr--prev').blur();
                            $('.fotorama__arr.fotorama__arr--next').blur();
                        })

                        .fotorama(
                            $.extend(window.skewerConfigs.oFotoramaConfig,{allowfullscreen: false})
                        );

                    if ( window.initMap !== undefined ) {
                        initMap();
                    }
                }
            });
        });
    });
}); // ready
