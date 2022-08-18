$(document).ready(function(){

    var oCartOpt = {
        count: '.js-b-basketmain .js-goods-count', // Путь к html-элементу числа текущего заказа
        total: '.js-b-basketmain .js-goods-total', // Путь к html-элементу общего числа заказа
        wrap_not_empty_basketmain: '.js_wrap_not_empty_basketmain',     // Путь к обертке непустой корзины
        wrap_empty_basketmain: '.js_wrap_empty_basketmain',             // Путь к обертке непустой корзины
        contentCls: 'basketmain__not-empty',     // Тело мини-корзины
        emptyCls: 'basketmain__empty'          // Скрытый контент мини-корзины
    };

    // Глобальная ф-ция обновления позиций мини-корзины. Используется и в cart.js
    window.updateMiniCart = function (jsonData) {

        var aData = jQuery.parseJSON(jsonData);

        var contentBlock = $(oCartOpt.wrap_not_empty_basketmain);
        var emptyBlock = $(oCartOpt.wrap_empty_basketmain);
        if ( contentBlock.is( '.' + oCartOpt.contentCls + '-hidden' ) ) {
            contentBlock.removeClass(oCartOpt.contentCls + '-hidden');
            emptyBlock.addClass(oCartOpt.emptyCls + '-hidden');
        }

        if (!aData.count) {
            contentBlock.addClass(oCartOpt.contentCls + '-hidden');
            emptyBlock.removeClass(oCartOpt.emptyCls + '-hidden');
        }

        if (aData.count) $(oCartOpt.count).text(aData.count);
        if (aData.total)
            $(oCartOpt.total).text(aData.total);
        else
            $(oCartOpt.total).text(0);
    };
});