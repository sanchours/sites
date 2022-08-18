Ext.require([
    'Ext.sk.layout.Center'
]);

Ext.define('Ext.Cms.AuthLayout',{
    extend: 'Ext.Viewport',
    layout: 'sk.center',
    margin: 0,
    padding: 5,
    form: null,
    sNameForm:'',

    initComponent: function(){

        var me = this;

        if(processManager.parseUrl('token')) {
            processManager.setData(this.path,{
                cmd: 'newPassForm',
                token: processManager.parseUrl('token')
            });
            // отослать
            processManager.postData();
        } else {
            me.form = createExt(me.sNameForm,me.path,me.lang);
            me.items = [me.form];
        }

        me.callParent();

    },

    execute: function(data, cmd){
        
        var me = this;

        switch (cmd) {
            case 'ForgotPass': 
            case 'Success': 
            case 'RecoveryForm':
                me.removeAll();
                me.form = createExt(cmd,me.path,data.lang);
                me.add(me.form);
                me.afterRender();
                break;
        } 
        if ( me.form )
            me.form.execute( data, cmd );

    }

});

/**
 *
 * @param sName
 * @param sPath
 * @param aLang
 * @return array()
 */
function createExt(sName,sPath,aLang) {

    var aForm = Ext.create('Ext.Cms.'+sName,{
        region: 'center',
        path: sPath,
        lang: aLang
    });
    return aForm;
}
