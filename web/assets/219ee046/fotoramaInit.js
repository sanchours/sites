// Перекрываем функцию фоторамы

// Дефолтный инициализатор
var defaultInitializer = $.fn.fotorama;

$.fn.fotorama = function(opts) {

    var $fotoramaDiv = $(this);

    $fotoramaDiv.on("fotorama:fullscreenenter", function () {

        $(".fotorama__stage__shaft")
            .mousedown(function () {
                flag = 0;
            })
            .mousemove(function () {
                flag = 1;
            })
            .mouseup(function (event) {

                if ( flag === 0 ){
                    var oFotoramaAPI = $fotoramaDiv.data("fotorama");

                    if ( oFotoramaAPI && !$(event.target).is(".fotorama__img, .fotorama__arr") ){
                        oFotoramaAPI.cancelFullScreen();
                    }

                }

            });

    });

    return defaultInitializer.call($fotoramaDiv, opts);
};