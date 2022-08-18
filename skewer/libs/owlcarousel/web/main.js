$(document).ready(function(){
    //carousel owl
    if ($('.js-owl-carousel').length) {
        $('.js-owl-carousel').each(function(index, el) {
            var carouselOptions = Object.assign({}, $(this).data('carouselOptions'), {lazyLoad: true});

            carouselOptions.nav === true ? $(this).addClass('owl-carousel--nav'): false;

            $(this).on('initialized.owl.carousel resized.owl.carousel resized.owl.carousel refreshed.owl.carousel dragged.owl.carousel translated.owl.carousel changed.owl.carousel', function( event ){
                // $(this).on('refreshed.owl.carousel changed.owl.carousel', function( event ){

                var eventItemIndex = event.item.index;
                var eventItemCount = event.item.count;
                var eventPageSize = event.page.size;

                if ( carouselOptions.nav ){
                    $(this).addClass('owl-carousel-nav');
                }

                if (carouselOptions.loop == true && carouselOptions.shadow == 1 ) {
                    $(this).addClass('owl-carousel-shadow-right owl-carousel-shadow-left')
                } else {
                    //show right shadow
                    if ( eventItemIndex !== eventItemCount - eventPageSize && carouselOptions.shadow == 1 ) {
                        $(this).addClass('owl-carousel-shadow-right');
                    } else {
                        $(this).removeClass('owl-carousel-shadow-right');
                    };
                    //show left shadow
                    if ( eventItemIndex > 0  && carouselOptions.shadow == 1) {
                        $(this).addClass('owl-carousel-shadow-left');
                    } else {
                        $(this).removeClass('owl-carousel-shadow-left');
                    };
                };

            }).owlCarousel(carouselOptions);

            $(this).trigger('refresh.owl.carousel');

        });

    }


});