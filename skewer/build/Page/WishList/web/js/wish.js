$(function() {

    function WishListPost(params, callback) {

        var popup = 'on';
        if (params.hasOwnProperty('popup')) {
            popup = params.popup;
            delete params.popup;
        }
        var obj = this;
        var setting = {
            moduleName: 'WishList',
            language: $('#current_language').val(),
            objectId: $(obj).data('id')

        };

        $.post('/ajax/ajax.php', $.extend(setting, params)).done(function (data) {
            return trigger.call(obj, JSON.parse(data));
        });

        function trigger(response) {
            if (typeof callback === 'function') {
                callback.call(this, response);
            }

            if ($('.wishs-count').length) {
                $('.wishs-count').html(response.data.count);
            }

            var block = $('#fancyWish');

            if (response.data.text && response.data.auth) {
                $.fancybox.open(response.data.text);
            } else if (popup == 'on') {
                $.fancybox.open(block.html(),{
                    dataType : 'html',
                    autoSize: true,
                    autoCenter: true,
                    openEffect: 'none',
                    closeEffect: 'none'
                });
            }
        }
        return false;
    }


    $('.js-add-wish').bind('click', function() {
        return WishListPost.call(this, {cmd: 'addItem'});
    });

    $('.js-wish-del-one').bind('click',function () {

        var wishRow = $(this).parents('.js-wish-row'),
            dataId = wishRow.data('id'),
            nameGood = wishRow.data('title'),
            messegeDelOne = $('#mesDelOne'),
            htmlMessage = messegeDelOne.html();

        htmlMessage = htmlMessage.replace('{1}',dataId);
        htmlMessage = htmlMessage.replace('{0}',nameGood);
        $.fancybox.open(htmlMessage,{
            dataType : 'html',
            autoSize: true,
            autoCenter: true,
            openEffect: 'none',
            closeEffect: 'none'
        });
        return false;
    });

    $(document).on('click','.js-wish-remove', function() {
        var dataId = $(this).data('id');
        var selectorFind = ".js-wish-row[data-id="+dataId+"]";
        return WishListPost.call($(selectorFind), {cmd: 'removeItem', popup: 'off'}, function(response) {
            var lengthWishRow = $(selectorFind).parent().find(".js-wish-row").length;
            if (lengthWishRow>1)
                $(selectorFind).remove();
            else
                $(selectorFind).parent().remove();

            if (response.data.count == 0) {
                $('.js-wish__content').hide();
                $('.js-wish__empty').show();
            }
            $.fancybox.close();
        });
    });

    $('.js-wish-clear').bind('click', function() {
        var messegeAboutDel = $('#mesAboutDel');
        $.fancybox.open(messegeAboutDel.html(),{
            dataType : 'html',
            autoSize: true,
            autoCenter: true,
            openEffect: 'none',
            closeEffect: 'none'
        });
        return false;
    });

    $(document).on('click','.js-wish-reset',function() {
        return WishListPost.call(this, {cmd: 'unsetAll', popup: 'off'}, function(response) {
            $('.js-wish__content').hide();
            $('.js-wish__empty').show();
            $('.b-pageline').hide();
            $.fancybox.close();
        });
    });

    $(document).on('click', '.js-wish-close', function(){
        $.fancybox.close();
        return false;
    });

});
