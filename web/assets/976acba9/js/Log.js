/**
 * Нижняя панель для отображения информации
 */
Ext.create('Ext.data.Store', {
    storeId:'InterfaceLogStorage',
    fields: ['text'],
    data: []
});

Ext.define( 'Ext.Cms.Log', {
    extend: 'Ext.panel.Panel',
    region: 'south',
    split: true,
//    width: 700,
    height: 300,
    autoScroll: true,
    collapsible: true,
    collapsed: true,
    floatable: false,
    title: '',
    margin: 0,

    lang: {
        logPanelHeader: 'logPanelHeader',
        clear: 'clear'
    },

    rowsCnt: 0,

    items: [{
        xtype: 'grid',
        store: 'InterfaceLogStorage',
        border: 0,
        hideHeaders: true,
        columns: [
            {dataIndex: 'text', flex: 1}
        ]
    }],
    tools: [],
    listeners: {
        expand: function(){
            this.doLayout();
        },
        afterrender: function(){

            var me = this;

            // выдено из-за гонки событий. иначе пока не получилось
            window.setTimeout(function () {

                if ( me.placeholder ) {

                    me.placeholder.on('click', function () {
                        me.expand();
                    });

                    me.header.on('click', function () {
                        me.collapse();
                    });

                }
            }, 100);



        }
    },

    generateTools: function(){
        var me = this;
        me.tools = [{
            type:'refresh',
            tooltip: me.lang.clear,
            handler: function(event, toolEl, panel){
                panel.up('panel').removeAllRows();
            }
        }];
    },

    initComponent: function(){

        var me = this;
        me.title = me.lang.logPanelHeader;

        this.generateTools();

        me.callParent();

        // навесить обработчики
        processManager.addEventListener('error',this.path, 'addRowError');
        processManager.addEventListener('log',this.path, 'addRowLog');
        processManager.addEventListener('request_add_info',this.path, 'requestAddInfo');

    },

    removeAllRows: function(){
        this.rowsCnt = 0;
        this.down('grid').getStore().removeAll();
    },

    addRow: function( text, title ){
        this.rowsCnt++;
        this.down('grid').getStore().add({
            text: '<strong>'+title+'</strong> '+text
        });
        this.doLayout();
    },

    addRowError: function( text ){
        var me = this;
        this.addRow(text,me.lang.err+':');
    },

    addRowLog: function( text ){
        var me = this;
        this.addRow(text,me.lang.log);
    },

    requestAddInfo: function( text ){
        var me = this;
        this.addRow(text,me.lang.log);
        sk.message('Ответ от сервера с отладочной информацией! (записей: '+this.rowsCnt+')','','msg-error');
    }

});
