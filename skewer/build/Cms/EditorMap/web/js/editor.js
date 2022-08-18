Ext.define('Ext.Cms.EditorMap', {
    extend: 'Ext.Viewport',
    renderData: {},
    message: null,
    border: 0,
    margin: '0 0 3 0',
    padding: 0,
    lang: {
        set_marker: 'set_marker'
    },
    childEls: ['body'],
    html: '',
    renderTpl: [
        ''
    ],
    execute: function( data, cmd ) {

        switch ( cmd ) {

            case "init":

                this.loadMap();

                break;

            case 'edit':

                this.update(data.html);

                var me = this;

                Ext.Loader.injectScriptElement(
                    data.urlScript,
                    Ext.emptyFn,
                    function() {
                        sk.error( 'Ошибка загрузки файла ['+ data.urlScript +']' )
                    },
                    window
                );

                $("#js_map_form").submit( function( event ){

                    event.preventDefault();

                    var data = {
                        cmd:         'save',
                        mapMode:     me.parseUrl('mapMode'),
                        geoObjectId: me.parseUrl('geoObjectId'),
                        entities:    me.parseUrl('entities'),
                        mapId:       me.parseUrl('mapId'),
                        mapData: {
                            zoom:       $("#js_map_zoom").val(),
                            center:     $("#js_map_center").val()
                        },
                        geoData:{
                            latitude:   $("#js_map_lat").val(),
                            longitude:  $("#js_map_lng").val(),
                            address:    $("#js_map_address").val()
                        }

                    };
                    processManager.setData(me.path,data);
                    processManager.postData();

                });


                break;

            case "save":

                var iRowId = data['iRowId'];

                this.saveSettings(iRowId);

                // Закрываем окно
                window.top.close();
                window.top.opener.focus();

                break;

        }

    },

    loadMap: function(){

        processManager.setData(this.path,{
            cmd:        this.parseUrl('cmd'),
            mapMode:    this.parseUrl('mapMode'),
            geoObjectId:   this.parseUrl('geoObjectId'),
            entities:   this.parseUrl('entities'),
            showModification: this.parseUrl('showModification'),
            mapId:      this.parseUrl('mapId')
        });
        processManager.postData();
    },


    parseUrl: function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if (null == results) {
            return '';
        }
        return results[1];
    },

    saveSettings: function( iRowId ) {

        if ( !window.top.opener )
            return false;

        var fireEventName,
            mapMode = this.parseUrl('mapMode');

        if ( mapMode == 'single' )
            fireEventName = 'closeEditorMap';
        else if ( mapMode == 'list' )
            fireEventName = 'closeEditorMapListMarkers';

        // старая админка
        if ( window.top.opener['processManager'] ){
            window.top.opener['processManager'].fireEvent( fireEventName, {
                ticket: this.parseUrl('ticket'),
                value:  iRowId
            });
        }

        // новая админка
        if ( window.top.opener['React'] ) {
            window.top.opener['sk'].setField(
              this.parseUrl('path'),
              this.parseUrl('fieldName'),
              iRowId
            );
        }

        return true;

    }


});


/**
 * Выполняет проверку значений широты, долготы
 * @param {String} lat - широта
 * @param {String} lng - долгота
 * @throws {Error} - выкинет исключение в случае если значения не пройдут валидацию
 */
function validateCoordinates(lat, lng){

    var editorProcess = processManager.getProcess('out'),
        lang = editorProcess.lang;

    lat = $.trim(lat);
    lng = $.trim(lng);

    if ( !lat || !lng )
        throw new Error(lang.all_fields_required);

    var NumberLat = Number( lat.replace(new RegExp('\,', 'gi'), ".") ),
        NumberLng = Number( lng.replace(new RegExp('\,', 'gi'), ".") );

    if ( isNaN(NumberLat) || isNaN(NumberLng) )
        throw new Error(lang.invalid_data_format);

    if ((NumberLat > 90) || (NumberLat < -90))
        throw new Error(lang.error_latitude_range);

    if ((NumberLng > 180) || (NumberLng < -180))
        throw new Error(lang.error_longtitude_range);

}
