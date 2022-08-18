/**
 * Поиск в шапке сайта
 */
$(function(){

    // блок в заголовке
    var head_block = $('.b-sevice');

    // если отсутствует - завершаем обработку
    if ( !head_block.length )
        return;

    // позиции для блока поиска
    var topMenuHeight = head_block.outerHeight(),
        topMenuPos = head_block.offset().top;

    // позиционирование
    $('.b-search_head').css({'top': topMenuPos, 'height': topMenuHeight});

    // событие для разворачивания блока
    $('.search__open').on('click', function() {
        var parentBlock = $(this).parents(".js-search-block");
        $(this).parents('.b-search_head').addClass('b-search_head_full');
        $('.search__btn').show();
        $(this).hide();
        $("form input", parentBlock).focus();
    });

    // событие для сворачивания блока
    $('.search__close').on('click', function() {
        $(this).parents('.b-search_head').removeClass('b-search_head_full');
        $('.search__btn').hide();
        $('.search__open').show();
    });

    var placeholder_mini_form = $(".js_mini_form_search input:first").prop('placeholder');

    $(".js_mini_form_search")
        .on('mouseenter focus', '.js_hide_placeholder',
            function(){
                $(this).prop('placeholder', '');
            }
        )
        .on('mouseleave blur',  '.js_hide_placeholder',
            function(){
                $(this).prop('placeholder', placeholder_mini_form);
            }
        );

});
