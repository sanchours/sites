/**
 * Шапка дизайнерской панели
 */
Ext.define('Ext.Design.Header',{
    extend: 'Ext.panel.Panel',
    region: 'north',
    baseCls: 'b-header-panel',
    border: 0,
    padding: '0 0 3 0',

    autoReload: true,

    dockedItems: [{
        xtype: 'toolbar',
        dock: 'bottom',
        ui: 'footer',
        defaults: {
            xtype: 'button',
            minWidth: 100
        },
        items: [
            {
                text: designLang.headFullVer,
                handler: function() {
                    frameApi.setDisplayUrl('/');
                }
            },{
                text: designLang.headToAdminText,
                handler: function() {
                    window.open('/admin/');
                }
            },{
                xtype: 'component', flex: 1
            },{
                icon: pmDir+'/img/reload.png',
                text: 'auto',
                minWidth: 50,
                enableToggle: true,
                pressed: true,
                toggleHandler: function( self, value ) {
                    var panel = self.up('panel');
                    panel.autoReload = value;
                }
            },{
                text: designLang.headRenewText,
                handler: function() {
                    var me = this.up('panel');
                    processManager.setData(me.path, {
                        cmd: 'DropCacheAndReload'
                    });
                    processManager.postData();
                }
            },{
                text: designLang.headExit,
                handler: function() {
                    var me = this.up('panel');
                    Ext.MessageBox.confirm(designLang.headExit, designLang.headExitConfirm, function(res){

                        if ( res !== 'yes' ) return;

                        processManager.setData(me.path, {
                            cmd: 'logout'
                        });
                        processManager.postData();

                    });
                }
            }
        ]
    }],

    initComponent: function() {

        this.callParent();

        processManager.addEventListener('get_auto_reload', this.path, this.getAutoReloadVal, this );

    },

    execute: function( data, cmd ) {

        switch ( cmd ) {

            case 'logout':

                if ( data.success ) {
                    frameApi.reloadAll();
                } else {
                    sk.error(designLang.headExitError);
                }

                break;

        }

    },

    /**
     * Отдает флаг автоматической перезагрузки интерфейса просмотра
     * @returns {boolean}
     */
    getAutoReloadVal: function() {
        return this.autoReload;
    }

});
