var fotoramaTimeOut;

jQuery(document).ready(function($) {

    // Инициализация фоторамы
    initFotorama();

    // Делаем фотораму "отзывчивой"
    setResponsiveFotorama();

    // Установить мин. высоту
    recountMinHeightFotorama();

});

$(window).resize(function(){
    clearTimeout(fotoramaTimeOut);
    fotoramaTimeOut = setTimeout(recountMinHeightFotorama, 500);
});

/** Инициализация фоторамы в слайдере */
function initFotorama(){

    if ($(".js-fotorama-slider").length) {
        var $mainSlider = $(".js-fotorama-slider"),
            config = $mainSlider.data('config'),
            option = {
                width: '100%',
                margin: 0,
                shadows: false,
                fit: 'cover',
                click: false,
                height: $(".js-fotorama-container").height(), // Высота указана "жестко" для резервирования места под фотораму, воизбежание эффекта схлопывания
                stopautoplayontouch: false,
                keyboard: true
            };

        // Перекрываем конфиг сгенерированный админкой
        config = $.extend( config, option );

        $mainSlider
            // fotorama полностью сформирована
            .on({
                'fotorama:ready': function() {
                    // Если навигацию надо показывать только при наведении
                    if ( config.arrows === true ){
                        $(".fotorama__wrap").addClass('fotorama__wrap--no-controls');
                    }
                },
                'fotorama:load': function() {
                    $('.js-fotorama-container').addClass('b-picture--show');
                }
            })
            .fotorama(config);
    };

}

/**
 * Получить минимальную высоту для фоторамы
 * @returns {string}
 */
function getMinHeight4Fotorama(){

    var windowWidth = window.innerWidth,
        aMinHeight = $(".js-fotorama-slider").data('minheight') || [],
        minHeight = '';

    var breakpoints = $(".js-breakpoints").data('breakpoints');

    if (!breakpoints)
      return minHeight;

    if (windowWidth < breakpoints['break_desktop_top'] && windowWidth >= breakpoints['break_desktop']) {
        minHeight = aMinHeight['minHeight1280'];
    } else if (windowWidth < breakpoints['break_desktop'] && windowWidth >= breakpoints['break_tablet']) {
        minHeight = aMinHeight['minHeight1024'];
    } else if (windowWidth < breakpoints['break_tablet'] && windowWidth >= breakpoints['break_mobile_down']) {
        minHeight = aMinHeight['minHeight768'];
    } else if (windowWidth < breakpoints['break_mobile_down']) {
        minHeight = aMinHeight['minHeight350'];
    }

    return minHeight;

}

/**
 * Пересчет минимал.высоты фоторамы
 */
function recountMinHeightFotorama() {

    var iMinHeight,
        config = {};

    config.minheight = ( iMinHeight = getMinHeight4Fotorama() )
        ? iMinHeight + 'px'
        : null;

    setOptionsFotoramaOnFly(config);

}

/** Сбрасывает высоту и устанавливаем коэффицент пропорций, для того чтобы слайдер стал резиновым */
function setResponsiveFotorama(){

    setOptionsFotoramaOnFly({
        'width': '100%',
        height: null,
        ratio: $(".js-fotorama-slider").data('ratiofirstimage')
    });

}

/** Установка параметров фоторамы на лету */
function setOptionsFotoramaOnFly( config ){

    if ($(".js-fotorama-slider").length) {
        var fotorama = $(".js-fotorama-slider"),
            fotoramObject = fotorama.data('fotorama');

    if ( fotoramObject )
        fotoramObject.setOptions(config);};

}