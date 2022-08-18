/**
 * Класс для отображения набора фотографий
 */
Ext.define('FileImageListModel', {
    extend: 'Ext.data.Model',
    fields: [
       {name: 'name'},
       {name: 'url'},
       {name: 'size', type: 'float'},
       {name:'lastmod', type:'date', dateFormat:'timestamp'}
    ]
});

Ext.define('Ext.Adm.FileImageListView', {

    extend: 'Ext.view.View',

    height: '100%',
    border: 0,
    cls: 'js_adm_image_file',
    overItemCls: 'x-item-over',
    itemSelector: 'div.thumb-wrap',
    emptyText: '',
    multiSelect: true,

    store: {
        model: 'FileImageListModel',
        data: []
    },
    plugins: [
        Ext.create('Ext.ux.DataView.DragSelector', {})
    ],

    listeners: {
        itemdblclick: function(){
            this.up().execute( {}, 'selectFile' );

        },
        itemkeydown: function( self, record, item, index, event ){

            // если кнопка удаления
            if ( event.getKey() === event.DELETE ) {

                // контейнер
                var cont = this.up('panel');
                if ( !cont ) return false;

                // запуск процедуры удаления
                cont.execute( {}, 'delete' );

            }

            return true;

        }
    },

    /**
     * HTML шаблон для элемента
     */
    tpl: [
        '<tpl for=".">',
            '<div class="thumb-wrap" id="{name}">',
            '<tpl if="thumb"><div class="thumb"><img src="{preview}" title="{name}"></div></tpl>',
            '<tpl if="!thumb"><div class="balnkthumb">',
                '<img src="{preview}" title="{name}">',
                '<span class="">{ext}</span></div>',
            '</tpl>',
            '<span class="x-editable">{shortName}</span></div>',
        '</tpl>',
        '<div class="x-clear"></div>'
    ],

    prepareData: function(data) {
        Ext.apply(data, {
            shortName: Ext.util.Format.ellipsis(data.name, 15)
        });
        return data;
    }
});
