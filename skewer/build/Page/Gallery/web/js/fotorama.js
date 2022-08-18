$(function () {
    var oFotoramaGallery = {
        nav: "thumbs",
        width: "100%",
        allowfullscreen: true,
        loop: true,
        arrows: true,
        keyboard: true,
        fit:"scaledown",
        maxheight: 600
    };

    if ($('.js-fotorama').length)
        $('.js-fotorama').fotorama(oFotoramaGallery);
});