$(document).ready(function() {

    $('a.single_3').fancybox({
        openEffect: 'fade',
        nextEffect: 'elastic',
        prevEffect: 'elastic',
        helpers : {
            title : {
                type : 'inside'
            }
        },
        arrows    : true,
        nextClick : true,
        mouseWheel: true,
        closeBtn: true,
        beforeShow: function () {
            var imgAlt = $(this.element).find("img").attr("alt");
            var dataAlt = $(this.element).data("alt");
            if (imgAlt) {
                $(".fancybox-image").attr("alt", imgAlt);
            } else if (dataAlt) {
                $(".fancybox-image").attr("alt", dataAlt);
            }
        }
    });



});

