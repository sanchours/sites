$(document).ready(function(){

    // Добавление позиции в корзину
    $(document).on('click','.js-btnBuy[href="#tocart"]',function() {
        var that = $(this),
            catalogboxItem = that.parents(".js_catalogbox_item:first"),
            oGood = catalogboxItem.data("ecommerce")
        ;

        var objectId = $(this).attr('data-id');

        var countInput = $('input[data-id=' + objectId + ']',catalogboxItem);
        var count = 1;

        if (countInput.length)
            count = parseInt( countInput.val() );

        var params = {};
        params.moduleName = 'Cart';
        params.cmd = 'setItem';
        params.objectId = objectId;
        params.count = count;
        params.language = $('#current_language').val();

        $.post('/ajax/ajax.php', params, function(response) {

            if (window.updateMiniCart !== undefined)
                updateMiniCart(response);

            var aData = $.parseJSON(response);

            if (count > 0) {

                ecommerce.sendDataChangeCart( oGood, 0, count );

                $.fancybox.open(aData.sTemplate,{
                    dataType : 'html'
                });
            } else alert('Введено неправильное количество товара');
        });

        return false;
    });

    $(document).on('click', '.js-basket-close', function(){

        $.fancybox.close();
        return false;
    });
});