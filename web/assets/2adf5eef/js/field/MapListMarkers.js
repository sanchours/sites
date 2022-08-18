/**
 * Поле "Карта(список маркеров)"
 */
Ext.define('Ext.sk.field.MapListMarkers', {
    extend:'Ext.sk.field.MapSingleMarker',
    alias: ['widget.mapListMarkers'],

    // при нажатии кнопки выбора
    onButtonClick: function() {

        processManager.fireEvent( 'openEditorMapListMarkers', {
            scope: this.fieldCont,
            mode:  this.selectMode,
            fnc:   'onSetValue'
        } );

    }

});