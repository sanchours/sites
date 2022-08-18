var speed = 300;
var opener;
var openPanel = function(){
    $(opener)
        .stop()
        .animate({
            left : 0
        },speed,function(){},false)
};

$(function(){

    opener = $('#skDesignFrameOpener');

    // фрейм редакторов
    opener
        .hover(function(){

            openPanel();

        },function(){})
    ;

    $('.frame__link')
        .click(function(){
            opener
                .stop()
                .animate({
                    left : 1-opener.width()
                },speed,function(){},false)

        })
    ;

});
