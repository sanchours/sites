/**
 * сетка для модуля
 */
Ext.define('Ext.Design.ZoneSelectorGrid', {

    extend: 'Ext.grid.Panel',

    cont: null,
    store: null,

    height: '100%',
    border: 0,

    columns: [{
        text: 'Module',
        dataIndex: 'id',
        hidden: true
    },{
        dataIndex: 'title',
        flex: 1,
        renderer: function( value, meta, rec ) {

            // для разделов типа "директория"
            if ( !rec.get('own') ) {
                meta.tdCls = 'tree-row-inherit';
            } else {
                meta.tdCls = 'tree-row-bold';
            }

            return value;

        }
    },{
        xtype: 'actioncolumn',
        width: 20,
        tdCls: 'tree-row',
        items: [{
            getClass: function(icon,rowIndex,rec) {

                if ( rec.get('own') )
                    return 'icon-delete';
                else
                return false;

            },
            handler: function(grid, rowIndex) {

                var rec = grid.getStore().getAt(rowIndex);

                if (!rec.get('own'))
                    return false;

                // активировать событие удаления  элемента
                processManager.getMainContainer(this).revertZone( rec.data );

                return false;
            }
        }]
    }],
    hideHeaders: true,
    multiSelect: false,

    listeners: {

        itemclick: function( model, record ){

            // активировать событие выбора элемента
            processManager.getMainContainer(this).selectZone( record.data.id );

        }
    }
});

/**
 * модель данных
 */
Ext.define('ZoneSelectorModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int' },
        {name: 'title', type: 'string'},
        {name: 'own', type: 'boolean'}
    ]
});

/**
 * хранилище
 */
Ext.define('ZoneSelectorStore', {
    extend: 'Ext.data.Store',
    model: 'ZoneSelectorModel',
    sorters: ['title']
});

/**
 * Система отображения списка шаблонов для редактора зон
 */
Ext.define('Ext.Design.ZoneSelector', {
    extend: 'Ext.panel.Panel',
    title: designLang.zoneSelectorPanelTitle,
    store: null,
    grid: null,
    autoScroll: true,
    split: true,
    collapsible: false,

    /**
     * Инициализация
     */
    initComponent: function() {

        // генерация объекта
        this.callParent();

        var container = this;

        container.store = Ext.create('ZoneSelectorStore', {
            cont: container
        });

        container.grid = Ext.create('Ext.Design.ZoneSelectorGrid', {
            extend: 'Ext.grid.Panel',
            cont: container,
            store: container.store
        });
        container.add( container.grid );

    },

    /**
     * Подсветка зоны в списке
     * @param id
     */
    highlightZone: function( id ) {

        var me = this;

        var index = me.store.getById( parseInt(id) );
        if ( index ) {
            me.grid.getSelectionModel().select( index );
        }

    }

});
