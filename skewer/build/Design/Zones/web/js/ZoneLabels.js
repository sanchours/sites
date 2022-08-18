/**
 * сетка для модуля
 */

/**
 * модель данных
 */
Ext.define('ZoneLabelsModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'name', type: 'string' },
        {name: 'title', type: 'string'},
        {name: 'own', type: 'boolean'}
    ]
});

/**
 * хранилище
 */
Ext.define('ZoneLabelsStore', {
    extend: 'Ext.data.Store',
    model: 'ZoneLabelsModel'
});


// Column Model shortcut array
var ModelZoneColumn = [{
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
}];

// declare the source Grid
Ext.define('Ext.Design.ZoneLabelsGrid', {
    extend: 'Ext.grid.Panel',
    viewConfig: {
        plugins: {
            ptype: 'gridviewdragdrop',
            dragText: designLang.zoneLabelDragText
        },
        listeners: {
            drop: function() {
                processManager.getMainContainer(this).saveLabels();
            }
        }
    },
    flex : 1,
    hideHeaders: true,
    multiSelect: false,
    border: 0,
    margins: 0,
    stripeRows: true,
    columns: ModelZoneColumn
});

// create the destination Grid
Ext.define('Ext.Design.ZoneAddLabelsGrid', {
    extend: 'Ext.grid.Panel',
    title: designLang.zoneAddLabelHeader,
    dropOnSort: true,
    viewConfig: {
        plugins: {
            ptype: 'gridviewdragdrop',
            dragText: designLang.zoneAddLabelDragText
        },
        listeners: {
            drop: function() {
                processManager.getMainContainer(this).saveLabels();
            },
            beforedrop: function(node,data) {
                // запретить сортировку в этой панели
                return !data.view.up('panel').dropOnSort;

            }
        }
    },
    flex : 1,
    hideHeaders: true,
    multiSelect: false,
    border: 0,
    margins: 0,
    stripeRows: true,
    columns: ModelZoneColumn
});

//Simple 'border layout' panel to house both grids
Ext.define('Ext.Design.ZoneLabels',{

    extend: 'Ext.panel.Panel',
    title: designLang.zoneLabelsPanelTitle,
    grid: null,
    gridAdd: null,
    autoScroll: true,
    split: true,
    collapsible: false,
    defaults: {
    },
    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    /**
     * Инициализация
     */
    initComponent: function() {

        var container = this;

        // создать хранилища
        var store = Ext.create('ZoneLabelsStore');
        var storeAdd = Ext.create('ZoneLabelsStore',{
            sorters: ['title']
        });

        // первая сетка
        container.grid = Ext.create('Ext.Design.ZoneLabelsGrid', {
            store: store
        });

        // вторая сетка
        container.gridAdd = Ext.create('Ext.Design.ZoneAddLabelsGrid', {
            store: storeAdd
        });

        // добавление элементов
        container.items = [
            container.grid,
            container.gridAdd
        ];

        // генерация объекта
        this.callParent();

    }

});
