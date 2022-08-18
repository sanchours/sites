/**
 * Модуль для отображения панелей ресайза изображений
 */
Ext.define('Ext.Adm.PhotoResizerList',{

    extend: 'Ext.tab.Panel',

//    defaults: {
//        padding: 5,
//        border: 0
//    },
    border: 0,
    xtype: 'tabpanel',
    plain: true,
    width: 800,
    height: 800,
    /** Отложенный рендеринг табов! */
    deferredRender: false,
    items: [],
//    html: 'Вы можете выбрать размер кадрирования фотографии, потянув за рамки фотографии',

    initComponent: function() {

        var me = this,
            itemId, item
            ;

        me.items = [];

        var format, formatName;

        for ( formatName in me.value['formatsData'] ) {
            format = me.value['formatsData'][formatName];
            var component = Ext.create('Ext.Adm.PhotoResizer',{
                title: format.title,
                formatName: format.name,
                format: format,
                crop: me.value['cropData']
            });

            me.items.push( component );
        }

        // отключаем заголовок у панели
        me.title = '';

        me.callParent();

//    },

//    execute: function( data ) {

//        var format, formatName;
//        var tabs = this.down('tabpanel');
//
//        for ( formatName in data['formatsData'] ) {
//            format = data['formatsData'][formatName];
//            var component = Ext.create('Ext.Adm.PhotoResizer',{
//                title: format.title,
//                formatName: format.name,
//                format: format,
//                crop: data['cropData']
//            });
//            tabs.add( component );
//            //component.execute();
//        }

//    },

    /**
     * сборщик данных для посылки
     * @return {Object}
     */
//    getData: function() {
//
//        var data = {};
//        var tabs = this.down('tabpanel');
//
//        // перебрать все вкладки
//        for ( var i=0 ; i<tabs.items.getCount() ; i++ ) {
//
//            var cropItem = tabs.items.get(i);
//
//            // если активны - запросить данные
//            if ( cropItem.isActivated() )
//                data[cropItem.formatName] = cropItem.getCropData();
//
//        }
//
//        return { cropData: data };
    }

});
