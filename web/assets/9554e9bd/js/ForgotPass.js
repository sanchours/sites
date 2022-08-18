/**
 * Форма авторизации
 */
Ext.define('Ext.Cms.ForgotPass', {

    extend: 'Ext.form.Panel',
    bodyPadding: 5,
    width: 350,
    topRatio: 0.4,
    
    layout: {
        type: 'table',
        columns: 2
    },
    defaults: {
        labelWidth: 75,
        anchor: '100%'
    },
    id:'content',

    // The fields
    defaultType: 'textfield',
    items: [],
    bbar: [],
    tbar: [],
    listeners: {
        afterrender: function(me){
            // выбрать первое поле
            me.down('textfield').focus();
        }
    },

    generateItems: function(){
        var me = this;
        me.items = [[{
            xtype: 'textfield',
            vtype: 'email',
            fieldLabel: me.lang.email_forgot,
            name: 'login',
            allowBlank: false,
            width: 300,
            colspan: 2
        }],{
            xtype: 'image',
            src:'/ajax/captcha.php?v='+Math.random()*1000,
            itemId: 'img_captcha',
            style: "float: left; margin-top: 3px;  margin-right: 95px; margin-left: 91px;border: 0;",
            height:40,
            width: 90
        },{
            xtype: 'textfield',
            name: 'captcha',
            allowBlank: false,
            style: "width: 60px; height: 26px; margin-top: 5px; font-size: 1.5em; margin-left: -115px;",
            width: 55,
            length: 4,
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        var form = field.up('form');
                        form.onSubmit();
                    }
                }
            }
        }];
    },

    generateBBar: function () {
        var me = this;
        me.tbar= [me.lang.forgotLoginPass];
        me.bbar = [
            {
                xtype: 'button',
                text: me.lang.back_check,
                href:'/admin',
                target:'_self'
            },"->",
            {
                xtype: 'button',
                text: me.lang.forgotSend,
                handler: function() {
                    this.up('form').onSubmit();
                }
            }];

    },


    initComponent: function(){
        var me = this;

        me.title = me.lang.passwords_recovery;
        me.generateBBar();
        me.generateItems();
        me.callParent();
    },

    execute: function( data, cmd ){

        switch ( cmd ) {
            case 'checkForgot':

                // если авторизация не удалась
                if ( !data['success'] ) {
                    if (data['captcha'])
                        sk.error( data['captcha']);
                    if (data['login'])
                        sk.error( data['login'] );
                    this.reloadImg(this.getComponent('img_captcha'));
                }
                break;
        }

    },
    
    onSubmit:function(){
        
        var form = this.getForm(),
            values = form.getValues();
        // собрать посылку
        values.cmd = 'CheckForgot';
        processManager.setData(form.path,values);
        processManager.postData();

    },

    reloadImg: function (obj) {

    if ( !obj ) return false;
    var date = new Date();
    var src = '/ajax/captcha.php?v='+Math.random()*1000+ date.getTime();

    var oImg = Ext.get(obj.el.id);
    oImg.set({ src: src});

    return false;
}
});