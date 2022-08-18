$(function(){

    $('input').inputmask();

    $('.js_slider').each(function() {

        var
            me = this,
            oInpMin = $( 'input[name$="[min]"]', $( me ).parents( '.js_filter_inputwrap')),
            oInpMax = $( 'input[name$="[max]"]', $( me ).parents( '.js_filter_inputwrap')),
            v_min = parseInt( oInpMin.val() ),
            v_max = parseInt( oInpMax.val() ),
            l_min = parseInt( oInpMin.attr( 'def' ) ),
            l_max = parseInt( oInpMax.attr( 'def' ) ),
            form = $(me).closest('form.js_form_filter'),
            config;

        config = {
            range: true,
            min: l_min,
            max: l_max,
            values: [ v_min, v_max ],
            slide: function( event, ui ) {
                oInpMin.val( ui.values[ 0 ] );
                oInpMax.val( ui.values[ 1 ] );
            },
        };

        if ( form.hasClass('js_indexed_search_engine') ){
            config = $.extend(config, {
                change: function( event, ui ) {
                    form.submit();
                }
            });
        }

        $( me ).slider(config);

        oInpMin.change( function() {

            var
                cur_min = parseInt( oInpMin.val() ),
                cur_max = parseInt( oInpMax.val() );

            if ( cur_min > cur_max ) {
                cur_min = cur_max - 1;
                oInpMin.val( cur_min );
            }


            $( me ).slider( { values: [ cur_min, cur_max ] } );

        });

        oInpMax.change( function() {

            var
                cur_min = parseInt( oInpMin.val() ),
                cur_max = parseInt( oInpMax.val() );

            if ( cur_min > cur_max ) {
                cur_max = cur_min + 1;
                oInpMax.val( cur_max );
            }


            $( me ).slider( { values: [ cur_min, cur_max ] } );

        });

    });


    $('form.js_form_filter').submit( function() {

        var view = skCatFilter.getParam( 'view' );
        var sort = skCatFilter.getParam( 'sort' );
        var way = skCatFilter.getParam( 'way' );

        if ( view != undefined )
            $(this).append('<input type="hidden" name="view" value="'+view+'">');

        if ( sort != undefined )
            $(this).append('<input type="hidden" name="sort" value="'+sort+'">');

        if ( way != undefined )
            $(this).append('<input type="hidden" name="way" value="'+way+'">');

        return true;
    });

    $("form.js_indexed_search_engine").on('click', '.js_pseudo_anchor', function(){
        var link = $(this).closest('label').data("link");
        var input = $(this).closest('label').siblings("input");

        if ( input.prop("disabled") && !input.prop("checked") )
            return false;

        document.location.href = link;

    });

    $("form.js_indexed_search_engine").on('click', '.js_anchor', function(e){
        e.preventDefault();
        var link = $(this).closest('label').data("link");
        document.location.href = link;
    });


    $("form.js_indexed_search_engine").on('change', 'input[type=checkbox]', function(){
        var link = $(this).siblings('label').data('link');
        document.location.href = link;
    });

    $( 'form.js_indexed_search_engine' ).on('change', 'input.js_fl_str', function(){
        var form = $(this).closest('form.js_form_filter');
        form.submit();
    });

    $('form.js_form_filter').on('click', '.js_clear_catalog_filter' , function() {

        var form = $(this).closest('form.js_form_filter');

        form.find('input.js_fl_str').val( '' );
        form.find('option').prop( 'selected', false );
        form.find('input[type=checkbox]').prop( 'checked', false );
        form.find('input.js_fl_num').each( function() {
            $( this ).val( $( this ).attr( 'def' ) );
        });

        form.submit();
        return false;
    });

    function getDropDownItem(state) {

        // option с value=0 возвращает местозаполнитель("Не выбрано")
        if (!parseInt(state.id)){
            return state.text;
        }

        var $elem = $(state.element);
        var img = $elem.data('img');
        var className = $elem.data('class') || '';
        var imgHtml = img ? '<img alt="" src="' + img + '"/>' : '';

        return $('<span class="' + className + '">' + imgHtml + '<span>'+ state.text +'</span></span>');
    }

    $('form select').select2({
        theme: "filter",
        minimumResultsForSearch: Infinity,
        templateResult: getDropDownItem,
        templateSelection: getDropDownItem,
    });

});
