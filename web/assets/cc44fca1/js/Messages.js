Ext.define('Ext.Cms.Messages',{
    extend: 'Ext.container.AbstractContainer',
    border: 0,
    margin: '0 0 3 0',
    padding: 0,

    renderData: {},

    childEls: ['message'],
    renderTpl: [
        ''
    ],

    initComponent:function(){

        var me = this;

        me.callParent();

        window.setInterval(function(){
            processManager.setData(me.path, { cmd: 'update'});
            processManager.postData();
        }, 900000);

        processManager.addEventListener( 'reloadMessageBar', this.path, 'reloadBar' );


    },

    reloadBar: function() {
        processManager.setData(this.path, { cmd: 'update'});
        processManager.postData();
    },

    execute: function( data, cmd ) {

        switch ( cmd ) {

            case 'init':
                this.update(data.message);
                break;

            case 'update':

                this.update(data.message);
                break;

        }
    }
});
