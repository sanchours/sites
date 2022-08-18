$(document).ready(function(){
    // $(window).load(function () {

    // инициализация табов
    $(".js-tabs").tabs({

        create: function( event, ui ){
            // Инициализация фоторамы в табах
            if ($('.js-catalog-fotorama').length){
                $('.js-catalog-fotorama').fotorama(window.skewerConfigs.oFotoramaConfig);
            }
        },

        //активация таба
        activate: function( event, ui ){
            //Карта в табе
            if ( ui.newPanel.attr('id').substr(5) == 'map' ){
                if ( window.initMap !== undefined )
                    initMap();
            }
        }
    });

    $(".js-selected-tab").click();

    if ($(".js-selected-tab").length>0){

        if ( !location.hash ){
            location.hash = "#js_tabs";
        }

    }

});
