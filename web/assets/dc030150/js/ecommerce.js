window.ecommerce = (function(){

    window.dataLayer = window.dataLayer || [];

    /** Отправка запроса в аналитику */
    function sendData( data ){
        window.dataLayer.push(data);
    }


    return {

        /**
         * Отправляет e-commerce данные об добавлении/удалении товаров
         * @var {object} oEcommerceObject - e-commerce данные товара
         * @var {int} iOldCount - количество товара в корзине(до добавления/удаления)
         * @var {int} iNewCount - количество товара в корзине(после добавления/удаления)
         */
        sendDataChangeCart: function( oEcommerceObject, iOldCount, iNewCount ){

            iOldCount = parseInt(iOldCount);
            iNewCount = parseInt(iNewCount);

            if ( iOldCount == iNewCount)
                return ;

            if( !oEcommerceObject )
                return;

            var data = {};

            if ( iOldCount < iNewCount){

                oEcommerceObject.quantity = iNewCount - iOldCount;

                data = {
                    "event": "addToCart",
                    "ecommerce": {
                        "currencyCode": "RUB",
                        "add": {
                            "products": [ oEcommerceObject ]
                        }
                    }
                };

                if ( oEcommerceObject.list )
                    data.ecommerce.add.actionField = { "list": oEcommerceObject.list };


            } else if ( iOldCount > iNewCount ){

                oEcommerceObject.quantity = iOldCount - iNewCount;

                data = {
                    "event": "removeFromCart",
                    "ecommerce": {
                        "currencyCode": "RUB",
                        "remove": {
                            "products": [ oEcommerceObject ]
                        }
                    }
                };

                if ( oEcommerceObject.list )
                    data.ecommerce.remove.actionField = { "list": oEcommerceObject.list };

            }


            sendData(data);

        },

        /**
         * Отправляет e-commerce данные об удалении товаров из корзины
         * @var {Array} aEcommerceGoods - массив ec-данных товаров
         */
        sendDataClearCart:  function( aEcommerceGoods ){

            var data = {
                "event": "removeFromCart",
                "ecommerce": {
                    "currencyCode": "RUB",
                    "remove": {
                        "products": aEcommerceGoods
                    }
                }
            };

            sendData(data);

        },

        /**
         * Отправка ec-данных о покупке товаров
         * @var {Array} aEcommerceGoods - массив ec-данных товаров
         * @var iOrderId - ид заказа
         */
        sendDataPurchase: function(aEcommerceObjects, iOrderId ){

            var data = {
                'event': 'transaction',
                'ecommerce': {
                    'purchase': {
                        'actionField': {'id': iOrderId},
                        'products': aEcommerceObjects
                    }
                }
            };

            sendData(data);

        },

        /**
         * Отправка ec-данных о покупке товаров из формы "Купить в один клик"
         * Отправляются две команды: добавление товара в корзину и покупка товара
         * @var {Array} aEcommerceGoods - массив ec-данных товаров
         * @var iOrderId - ид заказа
         */
        sendDataPurchaseFastBuy: function( aEcommerceObjects, iOrderId ){

            //Добавление в корзину
            sendData({
                "event": "addToCart",
                "ecommerce": {
                    "add": {
                        'actionField': { 'list': aEcommerceObjects[0].list },
                        "products": aEcommerceObjects
                    }
                }
            });


            // Покупка
            sendData({
                "event": "fastBuy",
                "ecommerce": {
                    'purchase': {
                        'actionField': { 'id': iOrderId },
                        'products': aEcommerceObjects
                    }
                }

            });

        },

        /**
         * Отправляет ec-данные о показе товаров из списка и детальной
         */
        sendEcommerceImpressionsDetailPage: function(){

            /** @var {Object} отправляемая посылка ec-даныных */
            var oData = {};

            /** @var {Array} Массив с ec-данными товаров, показанных из списка */
            var aImpressions = [];

            $(".js_ecommerce_viewlist").each(function(index, elem){
                aImpressions.push( $(elem).data('ecommerce') );
            });

            if ( aImpressions.length )
                oData.impressions =  aImpressions;


            var oDetailGood = $(".js_ecommerce_detailPage").data('ecommerce') || {};

            if ( !$.isEmptyObject( oDetailGood ) ){
                oData.detail =  {
                    "products": [ oDetailGood ]
                }
            }

            if ( !$.isEmptyObject(oData) ){
                oData.currencyCode = "RUB";
                sendData({"ecommerce": oData});
            }

        },

        /**
         *  Отправляет данные о клике по товару
         *  @var {Object} oGood - ec-данные товара
         *  @var {String} location - ссылка на детальную товара
         */
        sendDataGoodClick: function( oGood, location ){

            sendData({
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                        'actionField': {'list': oGood.list},
                        'products': [ oGood ]
                    }
                },
                'eventCallback': function() {
                    // перенаправление
                    document.location = location;
                }
            });

        }


    };

})();
