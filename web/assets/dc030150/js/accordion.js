$(function(){

    var speed = 'normal';
    var acc_container = $('.comp-list');
    var block_animation = false;
    if ( acc_container.length ) {

        var list = $('li', acc_container)
            .find('.js-content-comp').hide().end()
            .find('.js-title-comp')
            .removeClass('title_on')
            .click(function(){

                if ( block_animation ) return false;
                var _this = $(this);
                block_animation = true;

                // свернуть
                if ( _this.is('.title_on') ) {
                    $('.js-content-comp',_this.parents('li:first'))
                        .slideUp(
                        speed,
                        function(){
                            _this.removeClass('title_on');
                            block_animation = false;
                        }
                    );

                }

                // развернуть
                else {

                    _this.addClass('title_on');
                    $('.js-content-comp',_this.parents('li:first'))
                        .slideDown(
                        speed,
                        function(){
                            block_animation = false;
                        }
                    )


                }

                return true;

            })
        ;

        if ( !acc_container.hasClass('js-close-all') ) {
            list
                .filter(':first')
                .addClass('title_on')
                .each(function () {
                    $('.js-content-comp', $(this).parents('li:first')).show()
                })
                .end()
            ;
        }
    }

});