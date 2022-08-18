/**
 * Форма вывода сообщений
*/
Ext.define('Ext.Cms.Success', {

    extend: 'Ext.form.Panel',
    bodyPadding: 5,
    width: 350,
    topRatio: 0.4,
    bbar:[],

    // Fields will be arranged vertically, stretched to full width
    initComponent: function(){
        var me = this;
        me.title = me.lang.passwords_recovery;
        me.html = me.lang.msg_recover_instruct;

        me.bbar = [{
                xtype: 'button',
                text: me.lang.back_check,
                href:'/admin',
                target:'_self'
            }];
        me.callParent();
    },
    execute: function(data, cmd){
    }


});
