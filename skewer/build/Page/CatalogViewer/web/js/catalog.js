var skCatFilter = (function() {

    return {

        fields: {},

        findGetParam: function() {

            var search = window.location.search.substr(1);
            var me = this;

            search.split( '&' ).forEach( function( item ) {

                if ( item ) {
                    item = item.split( '=' );
                    var cur_name = decodeURIComponent( item[0] );

                    if ( cur_name.indexOf( '[]' ) > 0 ) {

                        cur_name = cur_name.substr( 0, cur_name.length - 2 );

                        if ( me.fields[ cur_name ] === undefined )
                            me.fields[ cur_name ] = [];

                        me.fields[ cur_name ].push( decodeURIComponent( item[1] ) );

                    } else {

                        me.fields[ cur_name ] = decodeURIComponent( item[1] );
                    }
                }

            });

            return this.fields;
        },

        getParam: function( name ) {

            if ( ! Object.keys( this.fields ).length )
                this.findGetParam();

            return this.fields[ name ];
        },

        addParam: function( name, value ) {

            if ( ! Object.keys( this.fields ).length )
                this.findGetParam();

            this.fields[ name ] = value;

        },

        getParamStr: function() {

            var str = [];

            for ( var key in this.fields ) {

                if ( typeof this.fields[key] == 'object' ) {

                    this.fields[key].forEach( function( item ) {
                        str.push( key + '[]=' + item );
                    });

                } else
                    str.push( key + '=' + this.fields[key] );
            }

            return str.join( '&' );
        },

        getURL: function() {

            return window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + this.getParamStr();
        },

        go: function() {

            document.location.href = this.getURL();
        }

    };

}());

$(function(){

    ecommerce.sendEcommerceImpressionsDetailPage();

    $(document).on('click', 'a.js_ecommerce_link', function( event ){

        event.preventDefault();

        var that = $(this),
            catalogboxItem = that.closest(".js_catalogbox_item"),
            oGood = catalogboxItem.data("ecommerce")
        ;

        ecommerce.sendDataGoodClick( oGood, that.attr('href') );

        window.location.href = that.attr('href');

    });

    $('body').on('click', '.js_view_control', function(){

        skCatFilter.addParam( 'view', $(this).attr('curval') );
        skCatFilter.go();

    });

    $('body').on('click', '.js_sort_control', function(){

        var cur_sort_field = $(this).attr('curval');
        var old_sort_field = $('input[name=sort]').val();
        var old_sort_way = $('input[name=way]').val();

        if ( !cur_sort_field )
            old_sort_way = 'down';

        if ( cur_sort_field == old_sort_field )
            old_sort_way = old_sort_way == 'down' ? 'up' : 'down';
        else
            old_sort_way = 'up';

        skCatFilter.addParam( 'sort', cur_sort_field );
        skCatFilter.addParam( 'way', old_sort_way );
        skCatFilter.go();
    });

    $('body').on('click', '.js_tab_sort_control', function(){

        var cur_sort_field = $(this).attr('curval');
        var old_sort_field = $('input[name=sort]').val();
        var old_sort_way = $('input[name=way]').val();

        if ( !cur_sort_field )
            old_sort_way = 'down';

        if ( cur_sort_field == old_sort_field )
            old_sort_way = old_sort_way == 'down' ? 'up' : 'down';
        else
            old_sort_way = 'up';

        document.location.href = 'http://' + window.location.host + window.location.pathname + '?sort=' + cur_sort_field + '&way=' + old_sort_way;

    });


    $('body').on('click', '.js_catalogbox_plus', function(){
        var input = $(this).parents('.js_catalogbox_inputbox').find('input[type=text]');
        var value = parseInt(input.val());
        if ( isNaN(value) )
            value = 0;
        if ( value >= 0 )
            input.val( value + 1 );
        else
            input.val( 1 );
    });

    $('body').on('click', '.js_catalogbox_minus', function(){
        var input = $(this).parents('.js_catalogbox_inputbox').find('input[type=text]');
        var value = parseInt(input.val());
        if ( isNaN(value) )
            value = 2;
        if (value > 1) {
            input.val( value - 1 );
        } else {
            input.val(1);
        }
    });
});
