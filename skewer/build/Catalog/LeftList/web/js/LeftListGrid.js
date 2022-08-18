/**
 * Хранилище для списка площадок
 */
Ext.define('Ext.Catalog.LeftListGrid', {

    extend: 'Ext.grid.Panel',

    cont: null,
    store: null,

    height: '100%',
    width: '100%',
    border: 0,

    columns: [{
        text: 'Module',
        dataIndex: 'id',
        hidden: true
    },{
        dataIndex: 'title',
        flex: 5,
        renderer: function( value, meta, rec ) {
            var classRec = 'sk-catalog-' + rec['data']['id'];
            meta.tdCls = classRec;
            return value;
        }
    }],
    hideHeaders: true,
    multiSelect: false,

    //features: [{
    //    id: 'group',
    //    ftype: 'groupingsummary',
    //    groupHeaderTpl: '{name}',
    //    hideGroupedHeader: true,
    //    remoteRoot: 'summaryData'
    //}],

    listeners: {

        itemclick: function( model, record ){

            // поставить блокировку отправки
            processManager.setBlocker();

            // активировать событие выбора элемента
            this.up('panel').selectItem( record.data.id );

            // снять блокировку отправки
            processManager.unsetBlocker();

            // отправить данные
            processManager.postDataIfExists();

        }
    }
});
