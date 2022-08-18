/**
 * Форма авторизации
 */
Ext.define('Ext.Cms.AuthForm', {

    extend: 'Ext.form.Panel',

    title: '',
    bodyPadding: 5,
    width: 350,
    topRatio: 0.4,

    // Fields will be arranged vertically, stretched to full width
    layout: 'anchor',
    defaults: {
        labelWidth: 75,
        anchor: '100%'
    },

    // The fields
    defaultType: 'textfield',
    items: [],
    bbar: [],
    listeners: {
        afterrender: function(me){
            // выбрать первое поле
            me.down('textfield').focus();
        }
    },

    generateItems: function(){
        var me = this;
        me.items = [{
            xtype: 'textfield',
            fieldLabel: me.lang.authLoginTitle,
            name: 'login',
            allowBlank: false
        },{
            fieldLabel: me.lang.authPassTitle,
            name: 'pass',
            inputType: 'password',
            allowBlank: true,
            xtype: 'textfield',
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
        me.bbar = [{
            text: me.lang.authForgotPass,
            handler: function() {
                me.onForgot();
            }
        },"->",
            {
                xtype: 'button',
                text: me.lang.authCanapeId,
                handler: function() {
                    var hash = window.location.hash.replace('#','%23');
                    window.location.replace("/sys.php?return_link="+window.location.pathname+hash);
                }
            },
            {
                xtype: 'button',
                text: me.lang.authLoginButton,
                formBind: true, //only enabled once the form is valid
                disabled: true,
                handler: function() {
                    this.up('form').onSubmit();
                }
            }];

    },


    initComponent: function(){
        var me = this;

        me.title = me.lang.authPanelTitle;
        me.generateBBar();
        me.generateItems();
        me.callParent();
    },

    execute: function( data, cmd ){

        var me = this;

        switch ( cmd ) {

            case 'login':

                // если авторизация не удалась
                if ( !data['success'] ) {
                    sk.error( data['notice'] );
                } else {
                    window.location.reload();
                }

                break;

            case 'init':
                if ( data['reload'] ) {
                    window.location.reload();
                }
                break;

        }

    },

    onSubmit:function(){

        // иниициализация
        var form = this.getForm(),
            values = form.getValues()
        ;

        // собрать посылку
        values.cmd = 'login';
        processManager.setData(form.path,values);

        // отослать
        processManager.postData();

    },
    
    onForgot:function(){
        var me = this;
        processManager.setData(me.path, { cmd: 'forgotPass' });
        processManager.postData();

    }

});
