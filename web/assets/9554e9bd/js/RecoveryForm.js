/**
 * Форма востановления пароля с вводом паролей
 */
Ext.define('Ext.Cms.RecoveryForm', {

    extend: 'Ext.form.Panel',
    bodyPadding: 5,
    width: 350,
    topRatio: 0.4,
    items:[],
    bbar:[],

    // Fields will be arranged vertically, stretched to full width
    initComponent: function(){
        var me = this;
        me.title = me.lang.passwords_recovery;

        me.items = [{
                xtype: 'textfield',
                inputType: 'password',
                name: 'password',
                fieldLabel: me.lang.new_pass,
                allowBlank: false,
                width: 300
        }, {
                xtype: 'textfield',
                inputType: 'password',
                name: 'wpassword',
                fieldLabel: me.lang.wpassword,
                allowBlank: false,
                width: 300,
                listeners: {
                    specialkey: function(field, e){
                        if (e.getKey() == e.ENTER) {
                            var form = field.up('form');
                            form.onSubmit();
                        }
                    }
                }
            }
        ];
        
        me.bbar = [{
            xtype: 'button',
            text: me.lang.back_check,
            href:'/admin',
            target:'_self'
        },'->',{
            xtype: 'button',
            text: me.lang.send,
            handler: function() {
                this.up('form').onSubmit();
            }
        }];
        me.callParent();
    },
    execute: function (data) {
        if (!data.success && data.notice) {
            sk.error(data.notice);
        }
    },
    onSubmit:function(){

        // иниициализация
        var form = this.getForm(),
            values = form.getValues();
        
        values.token = processManager.parseUrl('token');
        // собрать посылку
        values.cmd = 'recoveryPass';
        processManager.setData(form.path,values);
        // отослать
        processManager.postData();

    }



});
