/**
 * Дизайнерский режим
 * основной модуль
 */

$(function(){

    // если дизайнерский режим активен
    if ( !designObj.init() ) {

        // убрать обводку
        $('.g-ramaborder').removeClass('g-ramaborder');

        // убрать дополнительные элементы
        $('.b-desbtn').remove();

        return;

    } else  {
        $('.b-desbtn').show();
    }

    /**
     * Добавление функции, вызаваемой перед обновлением страницы
     */
    window.preReload = function() {
        $('<div></div>')
            .addClass('page_cover')
            .appendTo($('body'))
        ;
    };

    var version = $('body').attr('sklayer') || 'default';

    //noinspection JSUnresolvedFunction
    $.getJSON('/design/','mode=menu&sectionId='+designObj.sectionId+'&version='+version,function(data){

        // добавление выпадающих меню в html
        designObj.addMenuItemsToBody( data );

    });

    // Обработка позиции перетаскиваемых мышью блоков в шапке

    $( ".js-designDrag-left" )
        .draggable()
        .bind('dragstart',function( event ){

            if ( $(document).width() < 1240 )
                return false;

            return event.ctrlKey;

        }).bind('dragstop',function(){

            designObj.sendCSSParams( {
                'hValue':$(this).css('left'),
                'vValue':$(this).css('top'),
                'hPosition':'left',
                'paramPath': $(this).attr('skTag')
            } );

        });

    $( ".js-designDrag-right" )
        .draggable()
        .bind('dragstart',function( event ){

            if ( $(document).width() < 1240 )
                return false;

            return event.ctrlKey;

        }).bind('dragstop',function( event , ui){

            var container = $(this).parents('.js_dnd_wraper');
            if ( !container.length ) {
                alert('not parent container found for d&d operation');
                return;
            }

            var rightPos = container.width() - ui.position.left - $(this).width();
            //console.log( container, container.width(), ui.position.left, $(this).width() );

            $(this).css('left','auto');
            $(this).css('right',rightPos);
            designObj.sendCSSParams( {
                'hValue':rightPos,
                'vValue':$(this).css('top'),
                'hPosition':'right',
                'paramPath': $(this).attr('skTag')

            } );

        });

});
