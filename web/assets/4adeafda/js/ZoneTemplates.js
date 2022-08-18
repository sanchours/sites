/**
 * сетка для модуля
 */
Ext.define('Ext.Design.ZoneTemplatesGrid', {

    extend: 'Ext.grid.Panel',

    cont: null,
    store: null,

    height: '100%',
    border: 0,

    TPL_WEIGHT_NONE: 0,
    TPL_WEIGHT_PARENT: 1,
    TPL_WEIGHT_CURRENT: 2,

    columns: [{
        text: 'Module',
        dataIndex: 'id',
        hidden: true
    },{
        dataIndex: 'title',

        renderer: function( value, meta, rec ) {

            var me = this;

            switch ( rec.get('weight') ) {
                case me.TPL_WEIGHT_NONE:
                default:
                    meta.tdCls = 'tree-row-inherit';
                    break;
                case me.TPL_WEIGHT_PARENT:
                    break;
                case me.TPL_WEIGHT_CURRENT:
                    meta.tdCls = 'tree-row-bold';
                    break;
            }

            return value;

        },

        flex: 5
    }],
    hideHeaders: true,
    multiSelect: false,

    listeners: {

        itemclick: function( model, record ){

            // активировать событие выбора элемента
            processManager.getMainContainer(this).selectTemplate( record.data.id );

        }
    }

});

/**
 * модель данных
 */
Ext.define('ZoneTemplatesModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int' },
        {name: 'title', type: 'string'},
        {name: 'weight', type: 'int'}
    ]
});

/**
 * хранилище
 */
Ext.define('ZoneTemplatesStore', {
    extend: 'Ext.data.Store',
    model: 'ZoneTemplatesModel',
    groupField: 'category'
});

/**
 * Система отображения списка шаблонов для редактора зон
 */
Ext.define('Ext.Design.ZoneTemplates', {

    extend: 'Ext.panel.Panel',
    title: designLang.zonePagesTitle,
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

        container.store = Ext.create('ZoneTemplatesStore', {
            cont: container
        });

        container.grid = Ext.create('Ext.Design.ZoneTemplatesGrid', {
            extend: 'Ext.grid.Panel',
            cont: container,
            store: container.store,
            features: [Ext.create('Ext.grid.feature.Grouping', {groupHeaderTpl: '{name}' })]
        });
        container.add( container.grid );

    },

    /**
     * Подсветка шаблона в списке
     * @param id
     */
    highlightTpl: function( id ) {

        var me = this;

        var index = me.store.getById( parseInt(id) );
        if ( index ) {
            me.grid.getSelectionModel().select( index );
        }

    }

});
