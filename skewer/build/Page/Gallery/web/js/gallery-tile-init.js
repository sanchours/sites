$(function() {

    // gallery tile cols init
    var jqGalleryTile = $('.js-gallery-tile'),
        backendOptions = jqGalleryTile.data('config'),
        blockLoad = false,                                          // Флаг, блокирующий загрузку новых изображений
        bAllImagesLoaded = jqGalleryTile.data("all_images_loaded"); // Все изображения загружены ?

    jqGalleryTile.justifiedGallery( getConfig(backendOptions) ).on('jg.complete', function (e) {

        if ( $(".js_anchor_4justifiedGallery").offset().top > $(window).height() ){
            $(window).scroll(onScrollAction);
        }

    });

    /**
     * Конфиг для инициализации библиотеки
     * @param {Object} backendOptions - опции принятые с сервера
     */
    function getConfig( backendOptions ){

        return {
            margins: 3,
            rowHeight: adaptive.isMobile() ? 130 : backendOptions.rowHeight,
            maxRowHeight: adaptive.isMobile() ? 130 : backendOptions.maxRowHeight,
            captions: false,
            cssAnimation: true,
            randomize: Boolean(backendOptions.randomize),

            thumbnailPath: function (currentPath, width, height, image) {

                /** width, height - размеры текущей превьюшки(размеры высчитываются самой библиотекой)*/
                var jqImage = $(image),
                    oImageData = jqImage.data('images'),
                    path = currentPath,
                    diffsRatio = [],
                    formats = [],
                    relevantFormat,
                    perimeterAvailablePlace = width + height,
                    ratioAvailablePlace = width/height,
                    perimeterFormat, ratioFormat, diffRatio;

                oImageData.forEach( function( format, index ){
                    perimeterFormat = format.width + format.height;
                    if ( perimeterFormat > perimeterAvailablePlace ){
                        ratioFormat = Math.abs( format.width/format.height);
                        diffRatio = Math.abs(ratioAvailablePlace - ratioFormat);
                        formats.push(format);
                        diffsRatio.push(diffRatio);
                    }
                });

                if (!diffsRatio.length)
                    return currentPath;

                relevantFormat = formats[diffsRatio.indexOf(Math.min.apply(null, diffsRatio))];
                path = relevantFormat.file;

                return path;
            }
        };
    }

    function onScrollAction(event){

        if ( bAllImagesLoaded || blockLoad )
            return false;

        if( ($(window).scrollTop() + $(window).height()) > $(".js_anchor_4justifiedGallery").offset().top ) {

            var currentPageNumber = jqGalleryTile.data('page') || 1,
                nextPageNumber = currentPageNumber + 1;

            blockLoad = true;
            jqGalleryTile.data('page',nextPageNumber);

            $.ajax({
                url: '/ajax/ajax.php',
                method: 'POST',
                data: {
                    moduleName: 'Gallery',
                    cmd: 'getChunkImages4JustifiedGallery',
                    page: nextPageNumber,
                    sectionId: $("#current_section").val(),
                    albumalias: jqGalleryTile.data('albumalias')
                }

            }).done( function(sData) {

                if ( sData ){
                    var aData = $.parseJSON(sData);

                    if ( aData.bLastChunk )
                        bAllImagesLoaded = true;

                    if ( aData.images.length ){

                        aData.images.forEach(function( image, index ){
                            jqGalleryTile.append(image);
                        });

                        blockLoad = false;

                        //Инициализация добавленных изображений
                        jqGalleryTile.justifiedGallery( 'norewind' );

                    }
                }

            }).fail(function() {
                console.log( "ajax error" );
            });
        }
    }

});// ready