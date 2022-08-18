$(document).ready(function(){

    var ecommerceContainer = $(".js_ecommerce_data_for_buy");

    if ( ecommerceContainer.length ){
        // Покупка из формы оформления заказа
        ecommerce.sendDataPurchase( ecommerceContainer.data("ecommerce_objects"), ecommerceContainer.data("order_id") );
    }

    var selectTypePayment = $(document).find('.js-form-select[name="tp_pay"]');
    var selectTypeDelivery = $(document).find('.js-form-select[name="tp_deliv"]');

    if($(selectTypePayment).length && $(selectTypeDelivery).length) {
        $.ajax('/ajax/ajax.php',
            {
                method: 'post',
                data: {
                    moduleName: 'Cart',
                    cmd: 'getMessage',
                    idTypePayment: selectTypePayment.val(),
                    idTypeDelivery: selectTypeDelivery.val(),
                    language: $('#current_language').val()
                },
                success: function (mResponse) {
                    var oResponse = $.parseJSON(mResponse);

                    if (oResponse.messagePayment !== undefined)
                        updateMessage(selectTypePayment, oResponse.messagePayment);

                    if (oResponse.messageDelivery !== undefined && oResponse.messageDelivery !== '') {
                        var inputAddress = selectTypeDelivery.closest(".js-form").find('input[name="address"]');
                        if (inputAddress.length) {
                            inputAddress.val(oResponse.messageDelivery);
                        }

                    }

                }
            });


        updateMessage(selectTypePayment);

    }

    // Общая функция посылки команд для работы с корзиной и обновления изменённых параметров в заказе
    function sendParam(params, callback) {

        params.moduleName = 'Cart';
        params.language = $('#current_language').val();

        $.post('/ajax/ajax.php', params, function(jsonData) {

            var aData = jQuery.parseJSON(jsonData);

            // Обновить общую стоимость последней изменённой позиции в соответствии с данными от сервера
            if (params.id != undefined && aData.lastItem.id_goods == params.id) {

                var oItemRow = $('.js-cart__row[data-id=' + params.id + ']');

                oItemRow.find('.js_cart_amount').val(aData.lastItem.count);
                oItemRow.data("count_goods_before_recount", aData.lastItem.count);
                oItemRow.find('.js-item_total').text(aData.lastItem.total);
            }

            $('.total').text(aData.total);

            // Если указана ф-ция обратного вызова, то выполнить её
            if (typeof callback === 'function')
                callback(aData);

            // Обновить мини-корзину, если присутствует
            if (window.updateMiniCart !== undefined)
                updateMiniCart(jsonData);
        });
    }

    // Обертка модального окна
    // Вызывает callback в случаи положительного ответа
    function checkConfirm(text, callback) {

        var _self = this;
        $.fancybox.open({
            modal: true,
            type: 'html',
            src: text,
            afterClose: function (instance, current, e) {
                const button = e ? e.target || e.currentTarget : null;
                const value  = button ? $(button).data('value') : 0;
                if (value) {
                    callback.call(_self);
                }
            }
        });
    }

    // Удаление каталожной позиции
    $('body').on('click', '.js_cart_remove', function() {

        var me = this;
        var id_good = $(this).attr('data-id');

        var tableRow = $(this).closest(".js-cart__row"),
            oldCount = tableRow.data("count_goods_before_recount");

        checkConfirm.call(me, $('.js-cart-confirm-delete-goods').html(), function() {

            sendParam({cmd: 'removeItem', id: id_good}, function(aData) {

                // Удалить строку каталожной позиции
                $(me).parents('.js-cart__row').remove();

                // Если больше нет заказанных позиций, то очистить корзину
                if (!aData.count) {
                    $('.js_cart_content').hide();
                    $('.js_cart_empty').removeClass('cart__empty-hidden');
                }

                ecommerce.sendDataChangeCart( tableRow.data("ecommerce"), oldCount, 0 );

            });

        });

        return false;
    });

    // Уменьшение количества каталожной позиции
    $('body').on('click', ".js_cart_minus", function(){

        var me = this;
        var id_good = $(me).attr('data-id');

        var row = $('.js_cart_amount[data-id=' + id_good + ']');
        var count = parseInt(row.val());

        // если не число - поставить 1
        if ( isNaN(count) )
            count = 1;

        // меньше 1 быть не может
        if (count > 1)
            count--;
        else
            count = 1;

        var tableRow = $(this).closest(".js-cart__row"),
            oldCount = tableRow.data("count_goods_before_recount");

        sendParam({cmd: 'recountItem', id: id_good, count: count}, function(aData){

            var tableRow = $(me).closest(".js-cart__row");

            // Изменение цены товара
            $(".js_price_good", tableRow).html(aData.lastItem.price);

            // Удалить предупреждение об изменении цены
            $(".js_warning_about_not_actual_price", tableRow).remove();

            // Удалить кнопку "Пересчитать позицию"
            $(".js_recosting_price_good", tableRow).remove();

            ecommerce.sendDataChangeCart( tableRow.data("ecommerce"), oldCount, count );
        });
    });

    // Увеличение количества каталожной позиции
    $('body').on('click', ".js_cart_plus", function() {

        var me = this;
        var id_good = $(me).attr('data-id');

        var row = $('.js_cart_amount[data-id=' + id_good + ']');
        var count = parseInt(row.val());

        // проверка на валидность
        if (isNaN(count))
            count = 0;

        // увеличть на 1 (если меньше 0 - привести к 1)
        if (count >= 0)
            count++;
        else
            count = 1;

        var tableRow = $(this).closest(".js-cart__row"),
            oldCount = tableRow.data("count_goods_before_recount");

        sendParam({cmd: 'recountItem', id: id_good, count: count}, function(aData){

            var tableRow = $(me).closest(".js-cart__row");

            // Изменение цены товара
            $(".js_price_good", tableRow).html(aData.lastItem.price);

            // Удалить предупреждение об изменении цены
            $(".js_warning_about_not_actual_price", tableRow).remove();

            // Удалить кнопку "Пересчитать позицию"
            $(".js_recosting_price_good", tableRow).remove();

            ecommerce.sendDataChangeCart( tableRow.data("ecommerce"), oldCount, count );
        });


    });

    // Пересчитать сумму позиции заказа
    $('body').on('click', ".js_recosting_price_good", function() {

        var me = this;
        var id_good = $(me).attr('data-id');

        var row = $('.js_cart_amount[data-id=' + id_good + ']');
        var count = parseInt(row.val());

        // проверка на валидность
        if (isNaN(count))
            count = 0;

        sendParam({cmd: 'recountItem', id: id_good, count: count}, function(aData){

            //обновить блок с ценой
            var tableRow = $(me).closest(".js-cart__row");
            $(".js_price_good", tableRow).html(aData.lastItem.price);

            // Удалить предупреждение об изменении цены
            $(".js_warning_about_not_actual_price", tableRow).remove();

            // Удалить кнопку "Пересчитать позицию"
            $(".js_recosting_price_good", tableRow).remove();

        });

    });

    // Ручное изменение количества товара
    $('.js_cart_amount')
        .keypress(function(e){
            if (e.keyCode==13){
                $(this).blur();
            }
        })
        .blur(function() {

            var me = this;
            var id_good = $(me).attr('data-id');

            var countValue = parseInt($(me).val());
            if ( isNaN(countValue) )
                countValue = 1;

            if (countValue > 0 && countValue == $(me).val()){

                var tableRow = $(this).closest(".js-cart__row"),
                    oldCount = tableRow.data("count_goods_before_recount");

                sendParam({cmd: 'recountItem', id: id_good, count: countValue}, function(aData){

                    var tableRow = $(me).closest(".js-cart__row");

                    // Изменение цены товара
                    $(".js_price_good", tableRow).html(aData.lastItem.price);

                    // Удалить предупреждение об изменении цены
                    $(".js_warning_about_not_actual_price", tableRow).remove();

                    // Удалить кнопку "Пересчитать позицию"
                    $(".js_recosting_price_good", tableRow).remove();

                    ecommerce.sendDataChangeCart( tableRow.data("ecommerce"), oldCount, countValue );
                });

            } else
                alert($('#js_translate_msg_count_gt_zero').html());
        })
    ;

    // Очистка корзины
    $('body').on('click', '.js_cart_reset', function(){

        checkConfirm.call(this, $('.js-cart-confirm-clear-goods').html(), function() {

            var aEcommerceObjects = [];
            $(".js-cart__row").each(function(index, item){
                var temp = $(item).data('ecommerce');
                if ( temp ) {
                    temp.quantity = $(".js_cart_amount", $(item)).val();
                    aEcommerceObjects.push(temp);
                }
            });

            sendParam({cmd: 'unsetAll'}, function(aData){

                $('.js_cart_content').hide();
                $('.js_cart_empty').removeClass('cart__empty-hidden');

                ecommerce.sendDataClearCart( aEcommerceObjects );
            });
        });

        return false;
    });

    //замена типов оплаты при смене типов доставки
    //обновление стоимости доставки, если включено.
    $(document).on('change', '.js-form-select[name="tp_deliv"]', function () {

        var value = this.options[this.selectedIndex].value;
        var select = $(this);
        var fastBuy = select.parents('form').find('input[name="fastBuy"]');

        $.ajax('/ajax/ajax.php',
            {
                method: 'post',
                data: {
                    moduleName: 'Cart',
                    cmd: 'updateTypeDelivery',
                    id: value,
                    language: $('#current_language').val(),
                    fastBuy: fastBuy.val()
                },
                success: function (mResponse) {

                    var oResponse = $.parseJSON(mResponse);

                    if (oResponse.payments !== undefined)
                        typePayment(this, oResponse.payments);

                    if (oResponse.delivery !== undefined) {
                        updateDelivery(oResponse.delivery);
                        if (oResponse.delivery.address !== undefined) {
                            var inputAddress = select.closest(".js-form").find('input[name="address"]');
                            if (inputAddress.length) {
                                inputAddress.val(oResponse.delivery.address);
                            }
                        }
                    }

                }
            });

    });

    $(document).on('change', '.js-form-select[name="tp_pay"]', function () {

        var select = $(this);
        var value = select.val();

        $.ajax('/ajax/ajax.php',
            {
                method: 'post',
                data: {
                    moduleName: 'Cart',
                    cmd: 'getMessage',
                    idTypePayment: value,
                    language: $('#current_language').val()
                },
                success: function (mResponse) {
                    var oResponse = $.parseJSON(mResponse);
                    updateMessage(select, oResponse.messagePayment);
                }
            });
    });


    //обновление select type payment
    function typePayment(select,payments) {

        var selectPayment = $(document).find('.js-form-select[name="tp_pay"]');
        selectPayment.html('');

        $.each(payments.payment, function(key, object){
            selectPayment.get(0).options.add(new Option(object.title,object.id));
        });

        updateMessage(selectPayment,payments.message);

    }

    //обновление данных по доставке
    function updateDelivery(delivery) {

        var totalBlock = $(document).find('.js-cart-total-block');

        if(delivery.price_delivery !== undefined && isNaN(delivery.price_delivery)){
            totalBlock.find('.js-cart-delivery .js-delivery').html(delivery.price_delivery);
            totalBlock.find('.js-cart-delivery .js-currency').hide();
        }else{
            totalBlock.find('.js-cart-delivery .js-delivery').html(delivery.price_delivery);
            totalBlock.find('.js-cart-delivery .js-currency').show();
        }

        if(delivery.total_price_to_pay !== undefined){
            totalBlock.find('.js-cart-to-pay span').html(delivery.total_price_to_pay);
        }

    }

    function updateMessage(select,message){

        if(select.attr('name') == "tp_pay"){
            var classMessage = 'js-message-type-payment';
        }else{
            var classMessage = 'js-message-type-delivery';
        }


        if(message === undefined) {

            var messageBlock = $(document).find('.' + classMessage);
            if (messageBlock.length != 0) {
                messageBlock.html('');
            }
        }else{
            var messageBlock = $(document).find('.'+classMessage);
            if(messageBlock.length == 0){
                var selectParent = select.closest(".form__input");
                selectParent.append("<span class='"+classMessage+"'>"+message+"</span>");
            }else{
                messageBlock.html(message);
            }
        }
    }

});