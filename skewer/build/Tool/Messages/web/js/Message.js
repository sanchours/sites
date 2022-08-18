
Ext.define('Ext.Tool.Message',{

    extend: 'Ext.panel.Panel',

    region: 'center',
    width: '100%',
    height: '100%',
    autoScroll: true,
    border: 0,
    addText: '',
    layout: 'fit',
    sendId: null,
    message: null,

    viewPanel: null,
    items: [],


    initComponent: function() {

        var me = this;

        me.items = [
            me.viewPanel = Ext.create('Ext.Tool.MessageView',{
                path: me.path
            })

        ];

        me.callParent();
    },

    execute: function( data, cmd ) {

        var me = this;
        switch ( cmd ) {

            case 'load':

                me.viewPanel.update(data['message']);
                break;
        }

        return true;
    }
});