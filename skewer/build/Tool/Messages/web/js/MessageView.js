Ext.define('Ext.Tool.MessageView',{

    extend: 'Ext.AbstractComponent',
    border: 0,
    padding: 5,
    renderData: null,
    autoRender: false,
    value: false,
    width: '100%',
    imgMaxHeight: 300,
    renderTpl: '',

    initComponent: function(){

        var me = this;
        me.callParent();
    }

});