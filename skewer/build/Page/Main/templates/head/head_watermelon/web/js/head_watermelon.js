$(function() {

//top menu
    var $menutopList;
    var $menutopListWrap;
    var menutopListHeight;
    $('.js-menutop-haschild').on({
        'mouseenter': function() {
            $menutopListWrap = $(this).parents('.js-menutop-wrap');
            $menutopListParent = $(this).parents('.js-menutop-parent');
            $menutopList = $(this).find('.js-menutop-child');
            menutopListParent = $menutopListParent.outerHeight(true);
            menutopListHeight = $menutopList.outerHeight(true);

            if (menutopListParent > menutopListHeight) {
                $menutopListWrap.height(menutopListParent)
            } else {
                $menutopListWrap.height(menutopListHeight)
            }

        },
        'mouseleave': function() {
            $menutopListWrap = $(this).parents('.js-menutop-wrap');
            $menutopListWrap.css('height', 'auto');
        }
    });

});//ready